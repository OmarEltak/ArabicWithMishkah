<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\LegalChunk;
use App\Models\LegalDocument;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RagService
{
    public function __construct(
        private readonly EmbeddingService $embeddings,
        private readonly int $chunkSize = 900,
        private readonly int $chunkOverlap = 120,
        private readonly int $topK = 6,
    ) {}

    /**
     * True when the active connection is Postgres AND the pgvector extension
     * is loaded AND legal_chunks has the embedding_pgv column. Cached for the
     * request to avoid re-querying the schema on every search.
     */
    public static function usingPgVector(): bool
    {
        return (bool) Cache::driver('array')->rememberForever('rag.pgvector', function (): bool {
            try {
                if (DB::connection()->getDriverName() !== 'pgsql') {
                    return false;
                }
                if (! Schema::hasColumn('legal_chunks', 'embedding_pgv')) {
                    return false;
                }
                $hasType = (bool) DB::scalar("SELECT 1 FROM pg_type WHERE typname = 'vector'");

                return $hasType;
            } catch (\Throwable) {
                return false;
            }
        });
    }

    public static function fromConfig(): self
    {
        return new self(
            embeddings: EmbeddingService::fromConfig(),
            chunkSize: (int) config('lawyer.chunk_size', 900),
            chunkOverlap: (int) config('lawyer.chunk_overlap', 120),
            topK: (int) config('lawyer.top_k', 6),
        );
    }

    /**
     * Split text into overlapping chunks of roughly $chunkSize characters,
     * preferring sentence boundaries. Works for English and Arabic alike since
     * we operate on multibyte-safe lengths.
     *
     * @return array<int, string>
     */
    public function chunkText(string $text): array
    {
        $text = trim(preg_replace('/\s+\n/u', "\n", $text) ?? $text);
        if ($text === '') {
            return [];
        }

        $size = $this->chunkSize;
        $overlap = max(0, min($this->chunkOverlap, $size - 1));
        $length = mb_strlen($text);
        if ($length <= $size) {
            return [$text];
        }

        $chunks = [];
        $start = 0;
        while ($start < $length) {
            $end = min($length, $start + $size);
            // Try to align $end with a sentence/paragraph boundary.
            if ($end < $length) {
                $window = mb_substr($text, $start, $end - $start);
                $last = max(
                    mb_strrpos($window, "\n\n") !== false ? mb_strrpos($window, "\n\n") : -1,
                    mb_strrpos($window, '. ') !== false ? mb_strrpos($window, '. ') : -1,
                    mb_strrpos($window, '۔ ') !== false ? mb_strrpos($window, '۔ ') : -1, // Arabic full stop
                );
                if ($last > $size * 0.5) {
                    $end = $start + $last + 1;
                }
            }

            $chunk = trim(mb_substr($text, $start, $end - $start));
            if ($chunk !== '') {
                $chunks[] = $chunk;
            }

            if ($end >= $length) {
                break;
            }
            $start = max($end - $overlap, $start + 1);
        }

        return $chunks;
    }

    /**
     * Embed and store all chunks for a document. Returns chunk count.
     *
     * Supersedes any currently-active chunks instead of deleting them so the
     * legal audit trail (which version of a law was cited when) survives every
     * upstream amendment. Filters in search() automatically exclude superseded
     * rows.
     */
    public function indexDocument(LegalDocument $document): int
    {
        // Mark all currently-active chunks superseded; preserve history.
        LegalChunk::query()
            ->where('legal_document_id', $document->id)
            ->whereNull('superseded_at')
            ->update(['superseded_at' => now()]);

        $chunks = $this->chunkText($document->content);
        if (count($chunks) === 0) {
            $document->forceFill(['chunk_count' => 0, 'ingested_at' => now()])->save();

            return 0;
        }

        $version = (int) ($document->version ?? 1);

        $usePgv = self::usingPgVector();

        // Batch embeddings to keep payloads reasonable.
        $batches = array_chunk($chunks, 64);
        $position = 0;
        $modelName = $this->embeddings->modelName();
        DB::transaction(function () use ($document, $batches, &$position, $modelName, $version, $usePgv) {
            foreach ($batches as $batch) {
                $vectors = $this->embeddings->embedBatch($batch);
                foreach ($batch as $i => $text) {
                    $vector = $vectors[$i] ?? [];
                    $chunk = LegalChunk::create([
                        'legal_document_id' => $document->id,
                        'position' => $position++,
                        'content' => $text,
                        'embedding' => $vector,
                        'embedding_dim' => count($vector),
                        'embedding_model' => $modelName,
                        'version' => $version,
                    ]);

                    // Mirror to native pgvector column for SQL-side cosine.
                    if ($usePgv && count($vector) > 0) {
                        DB::statement(
                            'UPDATE legal_chunks SET embedding_pgv = ?::vector WHERE id = ?',
                            [self::pgvectorLiteral($vector), $chunk->id]
                        );
                    }
                }
            }
        });

        $document->forceFill([
            'chunk_count' => $position,
            'ingested_at' => now(),
        ])->save();

        return $position;
    }

    /**
     * Retrieve top-K most relevant chunks for a query. Loads chunks in pages
     * and computes cosine similarity in PHP. Suitable up to ~10k chunks.
     *
     * @param  array<string, mixed>  $filters  Reserved for future jurisdiction/language filters.
     * @return Collection<int, array{chunk: LegalChunk, score: float}>
     */
    public function search(string $query, ?int $k = null, array $filters = []): Collection
    {
        $k = $k ?? $this->topK;
        $queryVector = $this->embeddings->embedOne($query);
        if (count($queryVector) === 0) {
            return collect();
        }
        $queryVector = EmbeddingService::normalize($queryVector);

        if (self::usingPgVector()) {
            return $this->searchPgVector($queryVector, $k, $filters);
        }

        return $this->searchInPhp($queryVector, $k, $filters);
    }

    /**
     * Build the document-level scope from filter options.
     *
     * Supported filters:
     *   - jurisdiction: string — single ISO code (e.g. 'EG')
     *   - jurisdictions: array<string> — multiple ISO codes
     *   - category: string — single primary category (e.g. 'companies')
     *   - categories: array<string> — multiple categories
     *
     * Returns an array of LegalDocument ids matching the filters, or null
     * if no document-level filters were specified (full corpus).
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, int>|null
     */
    private function filterDocumentIds(array $filters): ?array
    {
        $hasFilter = ! empty($filters['jurisdiction']) || ! empty($filters['jurisdictions'])
            || ! empty($filters['category']) || ! empty($filters['categories']);
        if (! $hasFilter) {
            return null;
        }

        $q = LegalDocument::query();
        // Jurisdiction filter is PERMISSIVE — matches the requested code OR
        // null. User-pasted reference clauses and internal precedents
        // typically have no jurisdiction; we always want them retrievable
        // alongside the jurisdiction-tagged eastlaws corpus.
        if (! empty($filters['jurisdiction'])) {
            $j = $filters['jurisdiction'];
            $q->where(function ($qq) use ($j): void {
                $qq->where('jurisdiction', $j)->orWhereNull('jurisdiction');
            });
        }
        if (! empty($filters['jurisdictions']) && is_array($filters['jurisdictions'])) {
            $list = $filters['jurisdictions'];
            $q->where(function ($qq) use ($list): void {
                $qq->whereIn('jurisdiction', $list)->orWhereNull('jurisdiction');
            });
        }
        // Category filter is STRICT — when a user explicitly says
        // category=companies they want exactly that, not the whole corpus
        // dumping in via OR-null.
        if (! empty($filters['category'])) {
            $q->where('category', $filters['category']);
        }
        if (! empty($filters['categories']) && is_array($filters['categories'])) {
            $q->whereIn('category', $filters['categories']);
        }

        return $q->pluck('id')->all();
    }

    /**
     * Postgres + pgvector path: pushes top-K cosine to the database via the
     * <=> operator (cosine distance, lower is closer). Single round-trip
     * regardless of corpus size.
     *
     * @param  array<int, float>  $queryVector
     * @return Collection<int, array{chunk: LegalChunk, score: float}>
     */
    private function searchPgVector(array $queryVector, int $k, array $filters = []): Collection
    {
        $literal = self::pgvectorLiteral($queryVector);
        $docIds = $this->filterDocumentIds($filters);

        $sql = 'SELECT id, 1 - (embedding_pgv <=> ?::vector) AS score
             FROM legal_chunks
             WHERE superseded_at IS NULL
               AND embedding_pgv IS NOT NULL
               AND embedding_dim = ?';
        $bindings = [$literal, count($queryVector)];
        if ($docIds !== null) {
            if ($docIds === []) {
                return collect();
            }
            $placeholders = implode(',', array_fill(0, count($docIds), '?'));
            $sql .= ' AND legal_document_id IN ('.$placeholders.')';
            $bindings = array_merge($bindings, $docIds);
        }
        $sql .= ' ORDER BY embedding_pgv <=> ?::vector LIMIT ?';
        $bindings[] = $literal;
        $bindings[] = $k;

        $rows = DB::select($sql, $bindings);

        if ($rows === []) {
            return collect();
        }

        $ids = array_map(fn ($r) => (int) $r->id, $rows);
        $chunks = LegalChunk::query()->whereIn('id', $ids)->get()->keyBy('id');

        $out = [];
        foreach ($rows as $row) {
            $chunk = $chunks->get((int) $row->id);
            if ($chunk) {
                $out[] = ['chunk' => $chunk, 'score' => (float) $row->score];
            }
        }

        return collect($out);
    }

    /**
     * Fallback path: in-PHP cosine across all active chunks. Streams chunks
     * via raw DB cursor (no Eloquent hydration), decodes JSON only for the
     * embedding, scores with a bounded min-heap of size K so memory stays
     * O(K) instead of O(N), and hydrates Eloquent models only for the
     * winners. At 55k chunks this brings a search from ~35s/648MB down to
     * a few seconds with a flat memory profile.
     *
     * The query vector is assumed pre-normalized (caller does this in
     * search()), so the magnitude of the chunk vector is the only sqrt we
     * cannot pre-compute without a schema change. Cosine still beats
     * dot-product-on-unnormalized for accuracy, and the per-row overhead
     * is dominated by the inner loop, not the sqrt.
     *
     * @param  array<int, float>  $queryVector  Already L2-normalized.
     * @return Collection<int, array{chunk: LegalChunk, score: float}>
     */
    private function searchInPhp(array $queryVector, int $k, array $filters = []): Collection
    {
        $docIds = $this->filterDocumentIds($filters);
        if ($docIds === []) {
            return collect();
        }

        // Min-heap so we can drop the smallest score in O(log K) when a new
        // candidate beats it. SplMinHeap's compare puts smallest at top.
        // SplMinHeap default compare is $value2 - $value1 so the smallest
        // is at top(). When we override, we need to mirror that polarity to
        // keep min-at-top semantics for our [score, id] tuples.
        $heap = new class extends \SplMinHeap
        {
            protected function compare($a, $b): int
            {
                return $b[0] <=> $a[0];
            }
        };
        $expectedDim = count($queryVector);

        $q = DB::table('legal_chunks')
            ->whereNull('superseded_at')
            ->where('embedding_dim', $expectedDim)
            ->select(['id', 'embedding']);
        if ($docIds !== null) {
            $q->whereIn('legal_document_id', $docIds);
        }

        foreach ($q->cursor() as $row) {
            $raw = $row->embedding;
            if (! is_string($raw) || $raw === '') {
                continue;
            }
            $vec = json_decode($raw, true);
            if (! is_array($vec) || count($vec) !== $expectedDim) {
                continue;
            }
            $score = self::cosine($queryVector, $vec);
            if ($heap->count() < $k) {
                $heap->insert([$score, (int) $row->id]);
            } elseif ($score > $heap->top()[0]) {
                $heap->extract();
                $heap->insert([$score, (int) $row->id]);
            }
        }

        if ($heap->count() === 0) {
            return collect();
        }

        // Drain heap (ascending), then reverse so best score is first.
        $winners = [];
        while (! $heap->isEmpty()) {
            $winners[] = $heap->extract();
        }
        $winners = array_reverse($winners);

        $ids = array_map(fn ($w) => $w[1], $winners);
        $chunks = LegalChunk::query()->whereIn('id', $ids)->get()->keyBy('id');

        $out = [];
        foreach ($winners as [$score, $id]) {
            $chunk = $chunks->get($id);
            if ($chunk) {
                $out[] = ['chunk' => $chunk, 'score' => (float) $score];
            }
        }

        return collect($out);
    }

    /**
     * Format a float vector as a pgvector text literal: '[0.1,0.2,...]'.
     *
     * @param  array<int, float>  $vector
     */
    public static function pgvectorLiteral(array $vector): string
    {
        // pgvector accepts text input like '[1.2, 3.4]'. Emit fixed-precision
        // floats to avoid locale issues with sprintf.
        $parts = [];
        foreach ($vector as $v) {
            $parts[] = rtrim(rtrim(number_format((float) $v, 8, '.', ''), '0'), '.') ?: '0';
        }

        return '['.implode(',', $parts).']';
    }

    /**
     * Keyword/LIKE search across active chunks. Independent of embeddings —
     * useful as a fallback when the embedding provider is mocked (so cosine
     * is effectively zero for non-identical strings) or as a hybrid retrieval
     * complement to vector search even when real embeddings are configured.
     *
     * Splits the query on whitespace, drops 1-character noise, and OR-matches
     * each remaining term against chunk content. Score is the count of
     * distinct terms hit per chunk.
     *
     * @return Collection<int, array{chunk: LegalChunk, score: float}>
     */
    public function searchByKeyword(string $query, int $k = 4, array $filters = []): Collection
    {
        $terms = array_values(array_filter(
            preg_split('/\s+/u', trim($query)) ?: [],
            fn (string $t) => mb_strlen($t) >= 2,
        ));
        if ($terms === []) {
            return collect();
        }
        // Cap at 8 terms to keep the LIKE query bounded.
        $terms = array_slice(array_unique($terms), 0, 8);

        $docIds = $this->filterDocumentIds($filters);

        $q = LegalChunk::query()
            ->whereNull('superseded_at')
            ->where(function ($q) use ($terms): void {
                foreach ($terms as $t) {
                    $q->orWhere('content', 'like', '%'.$t.'%');
                }
            });
        if ($docIds !== null) {
            if ($docIds === []) {
                return collect();
            }
            $q->whereIn('legal_document_id', $docIds);
        }
        $rows = $q->with('document')->limit(200)->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        // Score each chunk by the number of terms it contains.
        $scored = $rows->map(function (LegalChunk $chunk) use ($terms) {
            $hits = 0;
            foreach ($terms as $t) {
                if (mb_stripos($chunk->content, $t) !== false) {
                    $hits++;
                }
            }

            // Normalise to [0,1] so it's comparable to cosine output.
            return ['chunk' => $chunk, 'score' => $hits / max(1, count($terms))];
        });

        return $scored->sortByDesc('score')->values()->take($k);
    }

    /**
     * Cosine similarity between two equal-length float vectors.
     *
     * @param  array<int, float>  $a
     * @param  array<int, float>  $b
     */
    public static function cosine(array $a, array $b): float
    {
        $dot = 0.0;
        $na = 0.0;
        $nb = 0.0;
        $len = count($a);
        for ($i = 0; $i < $len; $i++) {
            $av = (float) $a[$i];
            $bv = (float) ($b[$i] ?? 0);
            $dot += $av * $bv;
            $na += $av * $av;
            $nb += $bv * $bv;
        }
        if ($na <= 0 || $nb <= 0) {
            return 0.0;
        }

        return $dot / (sqrt($na) * sqrt($nb));
    }

    /**
     * Build a numbered context block suitable for inclusion in a prompt.
     *
     * @param  Collection<int, array{chunk: LegalChunk, score: float}>  $results
     */
    public function formatContext(Collection $results): string
    {
        if ($results->isEmpty()) {
            return '';
        }
        $lines = [];
        $maxChars = (int) config('lawyer.context_chunk_max_chars', 500);
        foreach ($results as $idx => $row) {
            /** @var LegalChunk $chunk */
            $chunk = $row['chunk'];
            $doc = $chunk->document;
            $title = $doc?->title ?? 'Unknown';
            $citation = sprintf('[%d] %s (chunk #%d)', $idx + 1, $title, $chunk->position);
            $body = $maxChars > 0 ? mb_substr($chunk->content, 0, $maxChars) : $chunk->content;
            $lines[] = $citation."\n".$body;
        }

        return implode("\n\n---\n\n", $lines);
    }
}
