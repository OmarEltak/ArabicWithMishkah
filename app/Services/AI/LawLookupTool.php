<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\LegalDocument;
use App\Services\Ingestion\EastlawsIngestService;
use Illuminate\Support\Facades\Log;

/**
 * Tool exposed to the drafting LLM. Eastlaws is the canonical source of legal
 * authority; the local KB is only used as a cache to avoid re-fetching the
 * same document repeatedly.
 *
 * Behaviour:
 *  - If eastlaws is enabled: search eastlaws every call (cache hit deduped by
 *    source_ref so re-ingest is a no-op).
 *  - After ingestion, run RAG against the local KB to surface the most
 *    relevant chunks.
 *  - If eastlaws is enabled and returns nothing → return NOT_FOUND. The
 *    drafting prompt instructs Claude to refuse rather than fabricate.
 *  - If eastlaws is disabled: fall back to local KB only (warning to user).
 */
class LawLookupTool
{
    /** Local hit threshold above which we trust the cache and skip eastlaws. */
    private const CACHE_HIT_SCORE = 0.85;

    public function __construct(
        private readonly RagService $rag,
        private readonly EastlawsIngestService $eastlaws,
    ) {}

    /**
     * Chunk IDs surfaced during the most recent execute() call. Reset on every
     * call. ContractDraftingService accumulates these across the tool-use loop
     * and feeds them to CitationVerifier post-draft.
     *
     * @var array<int, int>
     */
    private array $lastSeenChunkIds = [];

    /** @return array<int, int> */
    public function lastSeenChunkIds(): array
    {
        return $this->lastSeenChunkIds;
    }

    /** @return array<string, mixed> */
    public static function definition(): array
    {
        return [
            'name' => 'lookup_law',
            'description' => 'Look up legal authority (statute, code article, regulation, treaty, or constitution) '
                .'in the official legal database — the canonical statutory source. ALWAYS call this before citing '
                .'any law or legal proposition. Returns NOT_FOUND if no matching authority exists; in that case you '
                .'MUST refuse to draft that clause rather than invent citations. Prefer Arabic queries for MENA '
                .'jurisdictions. Pass `category` whenever the document type maps cleanly to one of the known '
                .'categories so the search scopes to that subset of the corpus and avoids irrelevant matches.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Concise search query in the document language (Arabic for Egyptian/Gulf laws).',
                    ],
                    'country_id' => [
                        // string-or-integer because some smaller open models (e.g. Llama 3.1 8b
                        // via Groq) emit "1" instead of 1 and fail strict integer validation.
                        // We coerce with (int) on the way in.
                        'type' => 'string',
                        'description' => '1=Egypt (default), 4=UAE, 7=Qatar, 9=Saudi Arabia, 5=Kuwait, 6=Bahrain. Send as string.',
                    ],
                    'category' => [
                        'type' => 'string',
                        'description' => 'Optional. Restrict retrieval to a domain. One of: civil-general, '
                            .'companies, commercial, labour, real-estate, family, procedural, criminal, tax, '
                            .'social-insurance, notarisation, arbitration, capital-markets, investment, '
                            .'insolvency, compliance, banking, enforcement.',
                    ],
                    'topic' => [
                        'type' => 'string',
                        'description' => 'Optional plain-language topic (helps reranking).',
                    ],
                ],
                'required' => ['query'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function execute(array $input): string
    {
        $this->lastSeenChunkIds = [];

        $query = trim((string) ($input['query'] ?? ''));
        $countryId = (int) ($input['country_id'] ?? 1);
        $category = isset($input['category']) ? trim((string) $input['category']) : '';
        if ($query === '') {
            return 'NOT_FOUND. Empty query.';
        }

        // Build filter scope — jurisdiction maps from country_id, category
        // is passed through. Both are optional; if neither set, search the
        // full corpus.
        $filters = [];
        $isoFromCountry = \App\Services\Ingestion\EastlawsIngestService::isoForCountryId($countryId);
        if ($isoFromCountry !== '') {
            $filters['jurisdiction'] = $isoFromCountry;
        }
        if ($category !== '') {
            $filters['category'] = $category;
        }

        // 1) Cache check — strong local match means we already ingested this.
        $local = $this->rag->search($query, k: 4, filters: $filters);
        $topScore = $local->isNotEmpty() ? (float) $local->first()['score'] : 0.0;
        if ($topScore >= self::CACHE_HIT_SCORE) {
            $this->recordSeen($local);

            return $this->formatResults($local, source: 'cache');
        }

        // 1b) Keyword fallback — if vector search came up empty (mock embeddings
        // or genuine miss), try term-overlap against indexed chunks before
        // calling out to eastlaws. Hybrid retrieval is the gold standard for
        // RAG anyway; this also makes the demo workable without paid embeddings.
        $keyword = $this->rag->searchByKeyword($query, k: 4, filters: $filters);
        if ($keyword->isNotEmpty() && (float) $keyword->first()['score'] >= 0.5) {
            $this->recordSeen($keyword);

            return $this->formatResults($keyword, source: 'keyword');
        }

        // 2) Eastlaws is the canonical source.
        if (! $this->eastlaws->isEnabled()) {
            // Local-only fallback when integration disabled.
            if ($local->isNotEmpty()) {
                $this->recordSeen($local);

                return $this->formatResults($local, source: 'local-only');
            }

            return 'NOT_FOUND. Eastlaws integration is not configured and the local knowledge base has no matching authority. '
                .'Do not fabricate citations.';
        }

        try {
            $stats = $this->eastlaws->bulkIngestQuery(
                user: null,
                query: $query,
                countryId: $countryId,
                maxDocuments: 2,
                maxPages: 1,
            );
        } catch (\Throwable $e) {
            Log::channel('ai')->warning('lookup_law: eastlaws fetch failed', ['error' => $e->getMessage(), 'query' => $query]);

            return 'NOT_FOUND. Eastlaws is unreachable right now: '.$e->getMessage().'. Do not fabricate citations.';
        }

        $found = ($stats['ingested'] ?? 0) + ($stats['skipped'] ?? 0);
        if ($found === 0) {
            return "NOT_FOUND. Eastlaws has no matching authority for query: \"{$query}\" "
                ."(country_id={$countryId}). Do not fabricate citations. Tell the user no authority was found.";
        }

        // 3) Re-query local RAG now that fresh docs are indexed.
        $local = $this->rag->search($query, k: 4);
        if ($local->isEmpty()) {
            // Edge case: eastlaws ingested but RAG can't surface a chunk (e.g. mock embeddings
            // failing semantic match). Return raw excerpts from the freshly-ingested docs.
            return $this->fallbackRecentDocs($stats['documents'] ?? []);
        }

        $this->recordSeen($local);

        return $this->formatResults($local, source: 'eastlaws');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array{chunk: \App\Models\LegalChunk, score: float}>  $local
     */
    private function recordSeen($local): void
    {
        foreach ($local as $row) {
            $id = $row['chunk']->id ?? null;
            if (is_int($id) && ! in_array($id, $this->lastSeenChunkIds, true)) {
                $this->lastSeenChunkIds[] = $id;
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array{chunk: \App\Models\LegalChunk, score: float}>  $local
     */
    private function formatResults($local, string $source): string
    {
        // How many chunk characters to surface back to the LLM. Tight token
        // budgets (e.g. Groq free tier @ 6K TPM) require trimming.
        $chunkMax = (int) config('lawyer.tool_chunk_max_chars', 600);
        // Cap the total number of citations we hand back per call.
        $maxRows = (int) config('lawyer.tool_max_results', 2);

        $lines = ["FOUND ({$source}):"];
        $count = 0;
        foreach ($local as $idx => $row) {
            if ($count >= $maxRows) {
                break;
            }
            $chunk = $row['chunk'];
            $doc = $chunk->document;
            $title = mb_substr((string) ($doc?->title ?? 'Unknown'), 0, 100);
            $verifiedAt = $doc?->last_verified_at?->toDateString() ?? 'never';
            $version = $doc?->version ?? 1;
            $stale = $doc?->isStale() ? ' [STALE]' : '';
            $lines[] = sprintf(
                "[%d] %s (#%d, v%d, verified=%s%s)\n%s",
                $idx + 1,
                $title,
                $chunk->position,
                $version,
                $verifiedAt,
                $stale,
                mb_substr($chunk->content, 0, $chunkMax),
            );
            $count++;
        }

        return implode("\n\n---\n\n", $lines);
    }

    /**
     * @param  array<int, array{id:int, title:string}>  $docRefs
     */
    private function fallbackRecentDocs(array $docRefs): string
    {
        if ($docRefs === []) {
            return 'NOT_FOUND. Ingestion reported success but no documents were located.';
        }
        $ids = array_map(fn ($d) => (int) $d['id'], $docRefs);
        $docs = LegalDocument::query()->whereIn('id', $ids)->get();
        $lines = ['FOUND (official legal database, raw excerpts — cosine search degraded):'];
        foreach ($docs as $idx => $doc) {
            $lines[] = sprintf(
                "[%d] %s\n%s",
                $idx + 1,
                $doc->title,
                mb_substr($doc->content, 0, 1500),
            );
        }

        return implode("\n\n---\n\n", $lines);
    }
}
