<?php

declare(strict_types=1);

namespace App\Services\Ingestion;

use App\Models\LegalDocument;
use App\Models\User;
use App\Services\AI\RagService;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class IngestionService
{
    public function __construct(
        private readonly RagService $rag,
        private readonly TextExtractor $extractor,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            rag: RagService::fromConfig(),
            extractor: new TextExtractor,
        );
    }

    public function ingestText(
        ?User $user,
        string $title,
        string $content,
        string $jurisdiction = '',
        string $language = 'en',
        string $source = 'paste',
        ?string $sourceRef = null,
        array $metadata = [],
        string $refreshPolicy = RefreshPolicy::STANDARD,
        ?string $category = null,
        array $tags = []
    ): LegalDocument {
        $clean = TextExtractor::collapseWhitespace($content);
        if (mb_strlen($clean) < 20) {
            throw new RuntimeException('Document is too short to ingest (minimum 20 characters).');
        }

        $now = now();
        // Only eastlaws-sourced documents are eligible for automated re-checks.
        // Pasted text and uploads have no canonical upstream to verify against.
        $nextCheckAt = $source === 'eastlaws'
            ? RefreshPolicy::nextCheckAt($refreshPolicy, $now)
            : null;

        $doc = LegalDocument::create([
            'user_id' => $user?->id,
            'title' => $title !== '' ? $title : 'Untitled document',
            'source' => $source,
            'source_ref' => $sourceRef,
            'jurisdiction' => $jurisdiction !== '' ? $jurisdiction : null,
            'category' => $category,
            'tags' => $tags ?: null,
            'language' => $language,
            'metadata' => $metadata,
            'content' => $clean,
            'content_hash' => FreshnessService::hashContent($clean),
            'refresh_policy' => $refreshPolicy,
            'version' => 1,
            'last_verified_at' => $source === 'eastlaws' ? $now : null,
            'next_check_at' => $nextCheckAt,
        ]);

        $this->rag->indexDocument($doc);

        app(AuditLogger::class)->log(
            action: 'document.ingested',
            subject: $doc,
            summary: 'Document ingested via '.$source,
            metadata: [
                'source' => $source,
                'language' => $language,
                'jurisdiction' => $jurisdiction,
                'chunk_count' => $doc->fresh()?->chunk_count,
                'refresh_policy' => $refreshPolicy,
            ],
            userId: $user?->id,
        );

        return $doc->fresh();
    }

    public function ingestUpload(
        ?User $user,
        UploadedFile $file,
        string $title = '',
        string $jurisdiction = '',
        string $language = 'en',
        ?string $category = null,
        array $tags = []
    ): LegalDocument {
        $original = $file->getClientOriginalName();
        $text = $this->extractor->extractFromUpload($file->getRealPath(), $original);
        $titleFinal = $title !== '' ? $title : pathinfo($original, PATHINFO_FILENAME);
        // Persist file under storage/app/legal/.
        $stored = $file->storeAs('legal', uniqid('doc_', true).'_'.$original);

        return $this->ingestText(
            user: $user,
            title: $titleFinal,
            content: $text,
            jurisdiction: $jurisdiction,
            language: $language,
            source: 'upload',
            sourceRef: $stored,
            metadata: ['original_name' => $original, 'mime' => $file->getMimeType()],
            category: $category,
            tags: $tags,
        );
    }

    public function ingestUrl(
        ?User $user,
        string $url,
        string $title = '',
        string $jurisdiction = '',
        string $language = 'en',
        ?string $category = null,
        array $tags = []
    ): LegalDocument {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Invalid URL.');
        }
        $response = Http::timeout(30)
            ->withHeaders(['User-Agent' => 'My-Lawyer-Ingestion/1.0'])
            ->get($url);
        if ($response->failed()) {
            throw new RuntimeException('Failed to fetch URL: HTTP '.$response->status());
        }
        $body = $response->body();
        $text = TextExtractor::stripHtml($body);
        $titleFinal = $title !== '' ? $title : self::guessTitle($body, $url);

        return $this->ingestText(
            user: $user,
            title: $titleFinal,
            content: $text,
            jurisdiction: $jurisdiction,
            language: $language,
            source: 'url',
            sourceRef: $url,
            metadata: ['fetched_at' => now()->toIso8601String()],
            category: $category,
            tags: $tags,
        );
    }

    private static function guessTitle(string $html, string $url): string
    {
        if (preg_match('/<title>(.*?)<\/title>/iu', $html, $m)) {
            return trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return parse_url($url, PHP_URL_HOST) ?: 'Web document';
    }
}
