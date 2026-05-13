<?php

declare(strict_types=1);

namespace App\Services\Ingestion;

use App\Models\LegalDocument;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Real eastlaws.com adapter. Uses EastlawsClient for credentialed access.
 *
 * Disclosure: eastlaws.com's robots.txt disallows automated crawlers. This
 * adapter is intended for use ONLY by paying subscribers acting on their own
 * behalf and within their subscription terms. Throttling is enforced
 * (configurable) and a hard limit is applied to bulk ingestion runs.
 */
class EastlawsIngestService
{
    public function __construct(
        private readonly EastlawsClient $client,
        private readonly IngestionService $ingestion,
        private readonly bool $enabled,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            client: EastlawsClient::fromConfig(),
            ingestion: IngestionService::fromConfig(),
            enabled: (bool) config('services.eastlaws.enabled', false),
        );
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->client->isConfigured();
    }

    /** @return array<int, array{id:int, name:string}> */
    public function listCountries(): array
    {
        return $this->client->listCountries();
    }

    /**
     * Search legislation and return the discovered document references
     * without fetching their bodies.
     *
     * @return array<int, array{id:int, recType:int, slug:string}>
     */
    public function searchLegislation(string $query, int $countryId = 1, int $pageNo = 1): array
    {
        $this->guard();
        $html = $this->client->searchLegislation($query, $countryId, $pageNo);

        return EastlawsClient::parseResults($html);
    }

    /**
     * Maps eastlaws's internal numeric country IDs to ISO 3166 codes the
     * rest of the system uses for the `jurisdiction` column. Mirrors the
     * country list that LawLookupTool's input_schema documents.
     *
     * @var array<int, string>
     */
    private const COUNTRY_ID_TO_ISO = [
        1 => 'EG',  // مصر
        2 => 'JO',  // المملكة الأردنية الهاشمية
        4 => 'AE',  // الإمارات
        5 => 'KW',  // الكويت
        6 => 'BH',  // مملكة البحرين
        7 => 'QA',  // قطر
        9 => 'SA',  // السعودية
        10 => 'OM', // سلطنة عمان
        11 => 'LY', // ليبيا (not in user-config but mapped for future)
        12 => 'TN', // تونس
        19 => 'LB', // لبنان
    ];

    /** Convert a numeric eastlaws country_id to an ISO code, defaulting to EG. */
    public static function isoForCountryId(int $countryId): string
    {
        return self::COUNTRY_ID_TO_ISO[$countryId] ?? 'EG';
    }

    /**
     * Fetch a single document and ingest it as a LegalDocument.
     * Skips if the same source+source_ref already exists (idempotent).
     *
     * `$countryId` carries the eastlaws country code that surfaced this
     * document via search, so the ingested LegalDocument gets the right
     * jurisdiction tag (was hardcoded to 'EG' before — bug fix).
     */
    public function fetchAndIngestOne(
        ?User $user,
        int $recId,
        int $recType,
        string $slug,
        int $countryId = 1,
        ?string $category = null,
        array $tags = []
    ): LegalDocument {
        $this->guard();

        $existing = LegalDocument::query()
            ->where('source', 'eastlaws')
            ->where('source_ref', self::sourceRef($recId, $recType))
            ->first();
        if ($existing instanceof LegalDocument && $existing->isIngestComplete()) {
            // Backfill category if the existing doc was ingested before
            // categorisation existed.
            if ($category && empty($existing->category)) {
                $existing->forceFill(['category' => $category])->save();
            }

            return $existing;
        }
        // If a stub exists, mark it indexing so concurrent jobs short-circuit
        // on the stub branch below and don't duplicate the fetch.
        if ($existing instanceof LegalDocument) {
            $existing->forceFill(['ingest_status' => LegalDocument::INGEST_INDEXING])->save();
        }

        try {
            $html = $this->client->fetchFullText($recId, $recType);
            $text = EastlawsClient::htmlToCleanText($html);
        } catch (\Throwable $e) {
            if ($existing instanceof LegalDocument) {
                $existing->forceFill(['ingest_status' => LegalDocument::INGEST_FAILED])->save();
            }
            throw $e;
        }

        if (mb_strlen($text) < 80) {
            if ($existing instanceof LegalDocument) {
                $existing->forceFill(['ingest_status' => LegalDocument::INGEST_FAILED])->save();
            }
            throw new RuntimeException("Eastlaws document {$recId}/{$recType} returned empty text.");
        }

        $title = EastlawsClient::extractTitleFromFullText($html) ?? "Eastlaws #{$recId}";
        $refreshPolicy = RefreshPolicy::classifyEastlaws($recType, $title);
        $now = now();
        $metadata = [
            'eastlaws_id' => $recId,
            'eastlaws_rec_type' => $recType,
            'eastlaws_country_id' => $countryId,
            'slug' => $slug,
            'fetched_at' => $now->toIso8601String(),
        ];

        // Stub-fill path: update the existing row in place and re-index.
        // Re-using the row preserves any FK references (e.g. citations from
        // chat sessions that already pointed at it) and the audit trail.
        if ($existing instanceof LegalDocument) {
            $existing->forceFill([
                'title' => $title,
                'content' => $text,
                'content_hash' => FreshnessService::hashContent($text),
                'jurisdiction' => self::isoForCountryId($countryId),
                'language' => 'ar',
                'category' => $category ?: $existing->category,
                'tags' => $tags ?: $existing->tags,
                'metadata' => array_merge(is_array($existing->metadata) ? $existing->metadata : [], $metadata),
                'refresh_policy' => $refreshPolicy,
                'version' => 1,
                'last_verified_at' => $now,
                'next_check_at' => RefreshPolicy::nextCheckAt($refreshPolicy, $now),
                'ingest_status' => LegalDocument::INGEST_COMPLETE,
            ])->save();

            // Re-index chunks. indexDocument supersedes any prior chunks first.
            app(\App\Services\AI\RagService::class)->indexDocument($existing->fresh());

            return $existing->fresh();
        }

        // No existing row — standard create path.
        return $this->ingestion->ingestText(
            user: $user,
            title: $title,
            content: $text,
            jurisdiction: self::isoForCountryId($countryId),
            language: 'ar',
            source: 'eastlaws',
            sourceRef: self::sourceRef($recId, $recType),
            metadata: $metadata,
            refreshPolicy: $refreshPolicy,
            category: $category,
            tags: $tags,
        );
    }

    /**
     * Create or return a placeholder row for a known upstream reference.
     * Idempotent: a second call with the same recId/recType returns the
     * existing row (whether stub or complete) without modification.
     */
    public function createStub(int $recId, int $recType, string $slug, int $countryId = 1, ?string $category = null): LegalDocument
    {
        $existing = LegalDocument::query()
            ->where('source', 'eastlaws')
            ->where('source_ref', self::sourceRef($recId, $recType))
            ->first();
        if ($existing instanceof LegalDocument) {
            return $existing;
        }

        return LegalDocument::create([
            'user_id' => null,
            'title' => $slug !== '' ? $slug : "Eastlaws #{$recId}",
            'source' => 'eastlaws',
            'source_ref' => self::sourceRef($recId, $recType),
            'jurisdiction' => self::isoForCountryId($countryId),
            'category' => $category,
            'language' => 'ar',
            'metadata' => [
                'eastlaws_id' => $recId,
                'eastlaws_rec_type' => $recType,
                'eastlaws_country_id' => $countryId,
                'slug' => $slug,
            ],
            'content' => '',
            'chunk_count' => 0,
            'ingest_status' => LegalDocument::INGEST_STUB,
            'refresh_policy' => RefreshPolicy::STANDARD,
            'version' => 1,
        ]);
    }

    /**
     * Run a search and ingest the top-N results.
     *
     * @return array{ingested:int, skipped:int, errors:int, documents:array<int, array{id:int, title:string}>}
     */
    public function bulkIngestQuery(
        ?User $user,
        string $query,
        int $countryId = 1,
        int $maxDocuments = 10,
        int $maxPages = 1,
        ?string $category = null,
        array $tags = []
    ): array {
        $this->guard();
        $maxDocuments = max(1, min($maxDocuments, 100));
        $maxPages = max(1, min($maxPages, 10));

        $stats = ['ingested' => 0, 'skipped' => 0, 'errors' => 0, 'documents' => []];

        for ($page = 1; $page <= $maxPages && count($stats['documents']) < $maxDocuments; $page++) {
            $refs = $this->searchLegislation($query, $countryId, $page);
            if ($refs === []) {
                break;
            }
            foreach ($refs as $ref) {
                if (count($stats['documents']) >= $maxDocuments) {
                    break;
                }
                try {
                    $existed = LegalDocument::query()
                        ->where('source', 'eastlaws')
                        ->where('source_ref', self::sourceRef($ref['id'], $ref['recType']))
                        ->exists();

                    $doc = $this->fetchAndIngestOne($user, $ref['id'], $ref['recType'], $ref['slug'], $countryId, $category, $tags);
                    if ($existed) {
                        $stats['skipped']++;
                    } else {
                        $stats['ingested']++;
                    }
                    $stats['documents'][] = ['id' => $doc->id, 'title' => $doc->title];
                } catch (\Throwable $e) {
                    Log::channel('ai')->warning('Eastlaws ingest failed for one doc', [
                        'rec_id' => $ref['id'],
                        'rec_type' => $ref['recType'],
                        'error' => $e->getMessage(),
                    ]);
                    $stats['errors']++;
                }
            }
        }

        return $stats;
    }

    private function guard(): void
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException(
                'Eastlaws integration is disabled. Set EASTLAWS_ENABLED=true and provide credentials in .env.'
            );
        }
    }

    private static function sourceRef(int $recId, int $recType): string
    {
        return $recType.':'.$recId;
    }
}
