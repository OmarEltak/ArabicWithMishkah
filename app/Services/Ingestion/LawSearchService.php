<?php

declare(strict_types=1);

namespace App\Services\Ingestion;

use App\Jobs\IngestEastlawsDocumentJob;
use App\Models\LegalDocument;
use App\Models\LegalSearchQuery;
use App\Services\AI\RagService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * User-driven law search. Combines local KB retrieval (vector + keyword) with
 * an upstream "peek" against the legal database to fill the result set when
 * local coverage is thin.
 *
 * The upstream peek is rate-controlled at two layers:
 *  1. legal_search_queries cache  — per-(query, jurisdiction) TTL prevents
 *     re-hitting upstream on repeated identical searches.
 *  2. EastlawsClient throttle     — global ~3s/request floor regardless of
 *     queue concurrency.
 *
 * No body fetches happen on the search path itself. The expensive work
 * (full text + embeddings) is dispatched as IngestEastlawsDocumentJob per
 * reference and runs out of band; stub rows track progress in the UI.
 */
class LawSearchService
{
    private const CACHE_TTL_DAYS = 7;

    /**
     * Minimum cosine similarity for an Arabic-script query. Real same-language
     * legal matches typically score 0.80+; we keep a generous margin so a
     * loose paraphrase still surfaces, while pruning the 0.30–0.55 noise band.
     */
    private const MIN_VECTOR_SCORE_AR = 0.65;

    /**
     * Stricter floor for non-Arabic queries. The Voyage multilingual model
     * maps English (or other Latin-script) tokens into the Arabic semantic
     * neighborhood, but the resulting matches are intrinsically weaker —
     * "Maritime transport" lit up unrelated telecom and capital-markets docs
     * at score 0.50 because the embedder finds *some* semantic overlap with
     * any "official document" chunk. Raising the floor here costs a few
     * legitimate cross-language hits but eliminates the noise.
     */
    private const MIN_VECTOR_SCORE_NON_AR = 0.75;

    /**
     * Minimum normalised term-overlap score for a keyword hit. Set above 0.50
     * so a multi-term query requires *more than half* its terms to overlap —
     * "Maritime transport" matching just "Transport" no longer surfaces an
     * unrelated road-traffic insurance law. Single-term queries score 1.0
     * when they hit, so they pass cleanly.
     */
    private const MIN_KEYWORD_SCORE = 0.66;

    public function __construct(
        private readonly RagService $rag,
        private readonly EastlawsIngestService $eastlaws,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            rag: RagService::fromConfig(),
            eastlaws: EastlawsIngestService::fromConfig(),
        );
    }

    /**
     * Run a user-driven search. Returns the merged result list immediately
     * and schedules upstream ingestion in the background when needed.
     *
     * @param  array{jurisdiction?: ?string, category?: ?string, country_id?: int}  $filters
     * @return array{
     *   results: array<int, array{document: LegalDocument, score: float, snippet: string, origin: string}>,
     *   total_local: int,
     *   total_upstream: ?int,
     *   cache: array{hit: bool, last_searched_at: ?string},
     *   triggered_ingest: int
     * }
     */
    public function search(string $rawQuery, array $filters = [], int $pageSize = 10): array
    {
        $query = trim($rawQuery);
        if (mb_strlen($query) < 2) {
            return [
                'results' => [],
                'total_local' => 0,
                'total_upstream' => null,
                'cache' => ['hit' => false, 'last_searched_at' => null],
                'triggered_ingest' => 0,
            ];
        }

        $jurisdiction = isset($filters['jurisdiction']) && $filters['jurisdiction'] !== ''
            ? (string) $filters['jurisdiction']
            : null;
        $category = isset($filters['category']) && $filters['category'] !== ''
            ? (string) $filters['category']
            : null;
        $countryId = isset($filters['country_id']) ? (int) $filters['country_id'] : 1;

        // 1) Local search — always, instant.
        $localDocs = $this->searchLocal($query, $jurisdiction, $category, $pageSize);
        $totalLocal = $localDocs->count();

        // 2) Cache lookup for this exact (normalized_query, jurisdiction).
        $normalized = LegalSearchQuery::normalize($query);
        $cacheRow = LegalSearchQuery::query()
            ->where('normalized_query', $normalized)
            ->where(function ($q) use ($jurisdiction): void {
                if ($jurisdiction === null) {
                    $q->whereNull('jurisdiction');
                } else {
                    $q->where('jurisdiction', $jurisdiction);
                }
            })
            ->first();

        $upstreamTriggered = 0;
        $totalUpstream = $cacheRow?->upstream_result_count;

        // 3) Decide whether to consult upstream.
        $shouldPeekUpstream = $this->eastlaws->isEnabled()
            && $totalLocal < $pageSize
            && ! ($cacheRow !== null && $cacheRow->isCacheFresh(self::CACHE_TTL_DAYS));

        // Refs already-known to belong to this query, from a previous peek.
        $cachedRefs = $this->refsFromCache($cacheRow);

        if ($shouldPeekUpstream) {
            try {
                $upstream = $this->peekUpstream($query, $countryId);
                $totalUpstream = count($upstream);

                $localRefs = $localDocs->pluck('document.source_ref')->filter()->all();
                $newRefs = array_values(array_filter(
                    $upstream,
                    fn (array $ref): bool => ! in_array($ref['recType'].':'.$ref['id'], $localRefs, true)
                ));

                // Create stubs for refs we don't already have. Cap to a
                // sensible per-search budget so a query with hundreds of
                // upstream hits doesn't fan out into hundreds of jobs.
                $newRefs = array_slice($newRefs, 0, max($pageSize, 25));

                foreach ($newRefs as $ref) {
                    $stub = $this->eastlaws->createStub(
                        recId: (int) $ref['id'],
                        recType: (int) $ref['recType'],
                        slug: (string) $ref['slug'],
                        countryId: $countryId,
                        category: $category,
                    );
                    // Only queue a fetch if the row really is a stub. Don't
                    // requeue failed rows here — the user retries those
                    // explicitly from the document page.
                    if ($stub->ingest_status === LegalDocument::INGEST_STUB) {
                        $stub->forceFill(['ingest_status' => LegalDocument::INGEST_QUEUED])->save();
                        IngestEastlawsDocumentJob::dispatch(
                            recId: (int) $ref['id'],
                            recType: (int) $ref['recType'],
                            slug: (string) $ref['slug'],
                            countryId: $countryId,
                            category: $category,
                        );
                        $upstreamTriggered++;
                    }
                }

                // Record all upstream refs in cache so re-runs of the same
                // query (within the TTL) surface the same doc set even if
                // their titles don't keyword-match the query phrase.
                $cachedRefs = array_map(
                    fn (array $r): string => $r['recType'].':'.$r['id'],
                    $upstream,
                );

                $this->upsertCacheRow(
                    normalized: $normalized,
                    raw: $query,
                    jurisdiction: $jurisdiction,
                    upstreamCount: $totalUpstream,
                    status: LegalSearchQuery::STATUS_SEARCHED,
                    refs: $cachedRefs,
                );
            } catch (\Throwable $e) {
                Log::channel('ai')->warning('LawSearchService: upstream peek failed', [
                    'query' => $query,
                    'error' => $e->getMessage(),
                ]);
                $this->upsertCacheRow(
                    normalized: $normalized,
                    raw: $query,
                    jurisdiction: $jurisdiction,
                    upstreamCount: $totalUpstream,
                    status: LegalSearchQuery::STATUS_RATE_LIMITED,
                    refs: $cachedRefs,
                );
            }
        } elseif ($cacheRow !== null) {
            // Increment search_count even on cache hit so popular-queries
            // analytics see this user's repeated interest.
            $cacheRow->increment('search_count');
        } else {
            // Local hit was sufficient but no cache row exists. Record the
            // search so popular-queries analytics see it, but leave upstream
            // count NULL — we didn't actually consult upstream.
            $this->upsertCacheRow(
                normalized: $normalized,
                raw: $query,
                jurisdiction: $jurisdiction,
                upstreamCount: null,
                status: LegalSearchQuery::STATUS_SEARCHED,
                refs: null,
            );
        }

        // Build the final list: local content matches + every doc that came
        // from this query's upstream peek (now or previously).
        $merged = $this->mergeWithCachedRefs($localDocs, $cachedRefs);

        // We only surface fully-indexed rows to the user. Stubs, queued,
        // indexing, and failed rows are invisible — but we count them so the
        // page can show "still working on N more" and keep polling.
        $visible = $merged->filter(fn (array $row): bool => $row['document']->isIngestComplete())
            ->values();
        $pendingCount = $merged->filter(fn (array $row): bool => in_array(
            $row['document']->ingest_status ?? LegalDocument::INGEST_COMPLETE,
            [LegalDocument::INGEST_STUB, LegalDocument::INGEST_QUEUED, LegalDocument::INGEST_INDEXING],
            true
        ))->count();

        // Keep ingested_count in sync with reality.
        $this->refreshIngestedCount($cacheRow ?? LegalSearchQuery::query()
            ->where('normalized_query', $normalized)
            ->where(function ($q) use ($jurisdiction): void {
                if ($jurisdiction === null) {
                    $q->whereNull('jurisdiction');
                } else {
                    $q->where('jurisdiction', $jurisdiction);
                }
            })
            ->first(), $cachedRefs);

        return [
            'results' => $this->formatResults($visible->take($pageSize)),
            'total_local' => $totalLocal,
            'total_upstream' => $totalUpstream,
            'pending_count' => $pendingCount,
            'cache' => [
                'hit' => $cacheRow !== null && $cacheRow->isCacheFresh(self::CACHE_TTL_DAYS),
                'last_searched_at' => $cacheRow?->last_searched_at?->toIso8601String(),
            ],
            'triggered_ingest' => $upstreamTriggered,
        ];
    }

    /**
     * Pull the cached upstream refs (source_ref strings) for this query.
     *
     * @return array<int, string>
     */
    private function refsFromCache(?LegalSearchQuery $row): array
    {
        if ($row === null) {
            return [];
        }
        $meta = is_array($row->metadata) ? $row->metadata : [];
        $refs = $meta['upstream_refs'] ?? [];

        return is_array($refs) ? array_values(array_filter($refs, 'is_string')) : [];
    }

    /**
     * Merge title/content matches with the documents discovered via upstream
     * peek (whether complete, indexing, or failed). Discovered docs take
     * priority — they're the user's intent — and are listed in the order
     * upstream returned them.
     *
     * @param  Collection<int, array{document: LegalDocument, score: float, snippet: string}>  $local
     * @param  array<int, string>  $cachedRefs
     * @return Collection<int, array{document: LegalDocument, score: float, snippet: string}>
     */
    private function mergeWithCachedRefs(Collection $local, array $cachedRefs): Collection
    {
        if ($cachedRefs === []) {
            return $local;
        }

        $byDocId = [];
        foreach ($local as $row) {
            $byDocId[(int) $row['document']->id] = $row;
        }

        // Pull every doc matching the cached refs in one query.
        $refDocs = LegalDocument::query()
            ->where('source', 'eastlaws')
            ->whereIn('source_ref', $cachedRefs)
            ->get();

        // Order by the upstream sequence so the user sees results in the
        // order eastlaws ranked them.
        $orderIndex = array_flip($cachedRefs);
        $sorted = $refDocs->sortBy(fn (LegalDocument $d): int => $orderIndex[(string) $d->source_ref] ?? PHP_INT_MAX)->values();

        foreach ($sorted as $doc) {
            $docId = (int) $doc->id;
            if (isset($byDocId[$docId])) {
                continue;
            }
            $byDocId[$docId] = [
                'document' => $doc,
                'score' => 1.0, // upstream-discovered: user explicitly searched for this set
                'snippet' => mb_substr((string) $doc->content, 0, 240),
            ];
        }

        // Sort: upstream-discovered first (in upstream order), then local-only
        // by score desc. We can detect "upstream-discovered" by ref presence.
        $refSet = array_flip($cachedRefs);
        $rowsOrdered = [];
        // First: refs in upstream order.
        foreach ($cachedRefs as $ref) {
            $match = collect($byDocId)->first(fn (array $row): bool => (string) $row['document']->source_ref === $ref);
            if ($match) {
                $rowsOrdered[(int) $match['document']->id] = $match;
            }
        }
        // Then: anything else.
        foreach ($byDocId as $docId => $row) {
            if (! isset($rowsOrdered[$docId])) {
                $rowsOrdered[$docId] = $row;
            }
        }

        return collect(array_values($rowsOrdered));
    }

    private function refreshIngestedCount(?LegalSearchQuery $row, array $cachedRefs): void
    {
        if ($row === null || $cachedRefs === []) {
            return;
        }
        $count = LegalDocument::query()
            ->where('source', 'eastlaws')
            ->whereIn('source_ref', $cachedRefs)
            ->where('ingest_status', LegalDocument::INGEST_COMPLETE)
            ->count();
        if ($count !== $row->ingested_count) {
            $row->forceFill(['ingested_count' => $count])->save();
        }
    }

    /**
     * Local-only search across the legal_documents table. Hybrid retrieval:
     * vector search (semantic) + keyword fallback (term overlap), plus a
     * title LIKE pass to surface stubs whose body isn't indexed yet.
     *
     * Results are deduplicated by document id and ordered by best score.
     *
     * @return Collection<int, array{document: LegalDocument, score: float, snippet: string}>
     */
    public function searchLocal(string $query, ?string $jurisdiction, ?string $category, int $k): Collection
    {
        $ragFilters = [];
        if ($jurisdiction) {
            $ragFilters['jurisdiction'] = $jurisdiction;
        }
        if ($category) {
            $ragFilters['category'] = $category;
        }

        // Pull a wider set of chunks so dedupe-by-document still yields ~k docs.
        $chunkLimit = max($k * 4, 20);

        $vectorFloor = $this->hasArabicChars($query) ? self::MIN_VECTOR_SCORE_AR : self::MIN_VECTOR_SCORE_NON_AR;
        $vector = $this->rag->search($query, k: $chunkLimit, filters: $ragFilters)
            ->filter(fn (array $row): bool => (float) ($row['score'] ?? 0) >= $vectorFloor);
        $keyword = $this->rag->searchByKeyword($query, k: $chunkLimit, filters: $ragFilters)
            ->filter(fn (array $row): bool => (float) ($row['score'] ?? 0) >= self::MIN_KEYWORD_SCORE);

        // Merge — for each doc, keep the best score across both retrievers.
        $byDoc = [];
        foreach ([$vector, $keyword] as $set) {
            foreach ($set as $row) {
                $chunk = $row['chunk'] ?? null;
                if ($chunk === null || ! $chunk->document) {
                    continue;
                }
                $docId = (int) $chunk->document->id;
                $score = (float) ($row['score'] ?? 0);
                if (! isset($byDoc[$docId]) || $score > $byDoc[$docId]['score']) {
                    $byDoc[$docId] = [
                        'document' => $chunk->document,
                        'score' => $score,
                        'snippet' => mb_substr((string) $chunk->content, 0, 240),
                    ];
                }
            }
        }

        // Title-LIKE pass to surface stubs (no chunks yet) and other docs the
        // chunk-based retrieval might miss when the body hasn't loaded.
        $titleQ = LegalDocument::query()
            ->where('title', 'like', '%'.$query.'%');
        if ($jurisdiction) {
            $titleQ->where(function ($q) use ($jurisdiction): void {
                $q->where('jurisdiction', $jurisdiction)->orWhereNull('jurisdiction');
            });
        }
        if ($category) {
            $titleQ->where('category', $category);
        }
        foreach ($titleQ->limit($chunkLimit)->get() as $doc) {
            $docId = (int) $doc->id;
            if (! isset($byDoc[$docId])) {
                $byDoc[$docId] = [
                    'document' => $doc,
                    'score' => 0.30, // weak signal, below vector/keyword hits
                    'snippet' => mb_substr((string) $doc->content, 0, 240),
                ];
            }
        }

        return collect(array_values($byDoc))
            ->sortByDesc('score')
            ->take($k)
            ->values();
    }

    /**
     * True when the query contains any Arabic-script characters. Used to
     * decide which vector-score floor to apply: same-language queries get
     * the lower (more permissive) threshold, cross-language queries the
     * stricter one.
     */
    private function hasArabicChars(string $query): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $query);
    }

    /**
     * Hit the upstream search-list endpoint. No body fetches happen here —
     * just the cheap result-list HTML parse.
     *
     * @return array<int, array{id:int, recType:int, slug:string}>
     */
    private function peekUpstream(string $query, int $countryId): array
    {
        if (! $this->eastlaws->isEnabled()) {
            return [];
        }

        return $this->eastlaws->searchLegislation($query, $countryId, 1);
    }

    /**
     * @param  array<int, string>|null  $refs  When non-null, replaces the
     *                                          cached upstream_refs array.
     */
    private function upsertCacheRow(
        string $normalized,
        string $raw,
        ?string $jurisdiction,
        ?int $upstreamCount,
        string $status,
        ?array $refs = null,
    ): void {
        $now = now();
        DB::transaction(function () use ($normalized, $raw, $jurisdiction, $upstreamCount, $status, $now, $refs): void {
            $row = LegalSearchQuery::query()
                ->where('normalized_query', $normalized)
                ->where(function ($q) use ($jurisdiction): void {
                    if ($jurisdiction === null) {
                        $q->whereNull('jurisdiction');
                    } else {
                        $q->where('jurisdiction', $jurisdiction);
                    }
                })
                ->lockForUpdate()
                ->first();

            if ($row instanceof LegalSearchQuery) {
                $meta = is_array($row->metadata) ? $row->metadata : [];
                if ($refs !== null) {
                    $meta['upstream_refs'] = $refs;
                }
                $row->forceFill([
                    'raw_query' => $raw,
                    'upstream_result_count' => $upstreamCount ?? $row->upstream_result_count,
                    'last_searched_at' => $now,
                    'status' => $status,
                    'search_count' => $row->search_count + 1,
                    'metadata' => $meta,
                ])->save();

                return;
            }

            LegalSearchQuery::create([
                'normalized_query' => $normalized,
                'raw_query' => $raw,
                'jurisdiction' => $jurisdiction,
                'upstream_result_count' => $upstreamCount ?? 0,
                'ingested_count' => 0,
                'search_count' => 1,
                'last_searched_at' => $now,
                'status' => $status,
                'metadata' => $refs !== null ? ['upstream_refs' => $refs] : null,
            ]);
        });
    }

    /**
     * Shape the merged collection into the UI-friendly array form.
     *
     * @param  Collection<int, array{document: LegalDocument, score: float, snippet: string}>  $docs
     * @return array<int, array{document: LegalDocument, score: float, snippet: string, origin: string}>
     */
    private function formatResults(Collection $docs): array
    {
        return $docs->map(function (array $row): array {
            /** @var LegalDocument $doc */
            $doc = $row['document'];

            return [
                'document' => $doc,
                'score' => (float) $row['score'],
                'snippet' => (string) $row['snippet'],
                'origin' => $doc->isIngestComplete() ? 'local' : 'pending',
            ];
        })->all();
    }
}
