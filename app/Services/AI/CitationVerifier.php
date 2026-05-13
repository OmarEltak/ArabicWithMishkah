<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\LegalChunk;

/**
 * Post-draft validator. Closes the gap between "we know cited law is current"
 * (Freshness) and "we know the AI quoted it accurately" (this).
 *
 * For every [n] marker in a draft, this:
 *  1. Extracts the surrounding sentence/clause as the "claim"
 *  2. Embeds the claim
 *  3. Computes cosine similarity against every chunk surfaced by lookup_law
 *     during the same drafting session
 *  4. Returns the best match + a verdict (verified / uncertain / unverified)
 *
 * Why this design:
 *  - The model uses [1], [2] markers but those are LOCAL to each lookup_law
 *    call — two calls would each return [1]. So a numeric map isn't reliable.
 *    Best-match search across all seen chunks is more robust.
 *  - We use the same embedding model the RAG uses, so the comparison is
 *    semantically consistent with how chunks were indexed.
 */
class CitationVerifier
{
    /** Above this cosine score we trust the citation. */
    public const VERIFIED_THRESHOLD = 0.70;

    /** Below this we flag it as unverified. Between is "uncertain". */
    public const UNCERTAIN_THRESHOLD = 0.50;

    public function __construct(
        private readonly EmbeddingService $embeddings,
    ) {}

    public static function fromConfig(): self
    {
        return new self(embeddings: EmbeddingService::fromConfig());
    }

    /**
     * Audit every [n] marker in a draft body.
     *
     * @param  array<int, int>  $toolChunkIds  IDs of every chunk surfaced by lookup_law during the draft
     * @return array<string, mixed>
     */
    public function audit(string $body, array $toolChunkIds): array
    {
        $markers = self::extractMarkers($body);
        if ($markers === []) {
            return ['markers' => [], 'tool_chunks_seen' => array_values($toolChunkIds), 'summary' => self::summary([])];
        }

        // Load every candidate chunk once. We embed query-side, compare against stored vectors.
        $chunks = LegalChunk::with('document')
            ->whereIn('id', $toolChunkIds)
            ->whereNull('superseded_at')
            ->get()
            ->keyBy('id');

        if ($chunks->isEmpty()) {
            // The model cited markers but lookup_law surfaced nothing usable —
            // every marker is unverified.
            $audit = [];
            foreach ($markers as $key => $info) {
                $audit[$key] = [
                    'marker' => $info['n'],
                    'snippet' => $info['snippet'],
                    'chunk_id' => null,
                    'document_id' => null,
                    'document_title' => null,
                    'document_version' => null,
                    'score' => 0.0,
                    'verdict' => 'unverified',
                ];
            }

            return ['markers' => $audit, 'tool_chunks_seen' => array_values($toolChunkIds), 'summary' => self::summary($audit)];
        }

        $audit = [];
        foreach ($markers as $key => $info) {
            $vector = $this->embeddings->embedOne($info['snippet']);
            if ($vector === []) {
                $audit[$key] = self::row($info, null, 0.0);

                continue;
            }
            $queryNorm = EmbeddingService::normalize($vector);

            $bestId = null;
            $bestScore = 0.0;
            foreach ($chunks as $chunk) {
                $vec = $chunk->embedding;
                if (! is_array($vec) || count($vec) === 0 || count($vec) !== count($queryNorm)) {
                    continue;
                }
                $score = RagService::cosine($queryNorm, $vec);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestId = $chunk->id;
                }
            }

            $audit[$key] = self::row($info, $bestId !== null ? $chunks[$bestId] : null, $bestScore);
        }

        return [
            'markers' => $audit,
            'tool_chunks_seen' => array_values($toolChunkIds),
            'summary' => self::summary($audit),
        ];
    }

    /**
     * Extract every [n] marker plus the surrounding sentence as the "claim".
     *
     * @return array<string, array{n:int, snippet:string}>
     */
    public static function extractMarkers(string $body): array
    {
        if (! preg_match_all('/\[(\d+)\]/u', $body, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $markers = [];
        foreach ($matches[1] as $idx => $hit) {
            $n = (int) $hit[0];
            $offset = $hit[1];

            // Walk back to the nearest sentence boundary, then forward to the next.
            $start = self::sentenceStart($body, $offset);
            $end = self::sentenceEnd($body, $offset);
            $snippet = trim(mb_substr($body, $start, $end - $start));
            // Strip the marker itself from the snippet (it's not the "claim").
            $snippet = preg_replace('/\['.preg_quote((string) $n, '/').'\]/u', '', $snippet) ?? $snippet;
            $snippet = trim(preg_replace('/\s+/u', ' ', $snippet) ?? $snippet);

            $key = 'm'.($idx + 1).'_n'.$n;
            $markers[$key] = ['n' => $n, 'snippet' => $snippet];
        }

        return $markers;
    }

    /**
     * @param  array{n:int, snippet:string}  $info
     * @return array<string, mixed>
     */
    private static function row(array $info, ?LegalChunk $chunk, float $score): array
    {
        $verdict = match (true) {
            $score >= self::VERIFIED_THRESHOLD => 'verified',
            $score >= self::UNCERTAIN_THRESHOLD => 'uncertain',
            default => 'unverified',
        };

        return [
            'marker' => $info['n'],
            'snippet' => $info['snippet'],
            'chunk_id' => $chunk?->id,
            'document_id' => $chunk?->legal_document_id,
            'document_title' => $chunk?->document?->title,
            'document_version' => $chunk?->version,
            'score' => round($score, 4),
            'verdict' => $verdict,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $audit
     * @return array<string, int>
     */
    private static function summary(array $audit): array
    {
        $tally = ['verified' => 0, 'uncertain' => 0, 'unverified' => 0, 'total' => 0];
        foreach ($audit as $row) {
            $verdict = (string) ($row['verdict'] ?? 'unverified');
            $tally[$verdict] = ($tally[$verdict] ?? 0) + 1;
            $tally['total']++;
        }

        return $tally;
    }

    private static function sentenceStart(string $body, int $offset): int
    {
        $window = mb_substr($body, 0, $offset);
        $candidates = [
            mb_strrpos($window, '. '),
            mb_strrpos($window, "\n"),
            mb_strrpos($window, '. '), // Arabic full stop is decoded similarly
            mb_strrpos($window, '. '), // U+06D4
        ];
        $best = -1;
        foreach ($candidates as $c) {
            if ($c !== false && $c > $best) {
                $best = $c;
            }
        }
        if ($best < 0) {
            // Fall back to a 240-char window before the marker.
            return max(0, $offset - 240);
        }

        return $best + 1;
    }

    private static function sentenceEnd(string $body, int $offset): int
    {
        $length = mb_strlen($body);
        $window = mb_substr($body, $offset, min(400, $length - $offset));
        $relCandidates = [
            mb_strpos($window, '. '),
            mb_strpos($window, "\n"),
            mb_strpos($window, '. '),
        ];
        $first = false;
        foreach ($relCandidates as $c) {
            if ($c !== false && ($first === false || $c < $first)) {
                $first = $c;
            }
        }
        if ($first === false) {
            return min($length, $offset + 240);
        }

        return $offset + $first + 1;
    }
}
