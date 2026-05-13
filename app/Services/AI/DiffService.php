<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\LegalChunk;
use App\Models\LegalDocument;

/**
 * Compares two snapshots of a legal document and produces a per-chunk diff
 * suitable for rendering side-by-side in the UI.
 *
 * Anchoring strategy:
 *   - Chunks have a stable `position` within a version. We pair chunks of the
 *     same position across versions, which works well when an amendment
 *     changes wording in place but doesn't reorder.
 *   - When positions don't line up (insertions/deletions), the unmatched
 *     chunk shows up as added/removed.
 *
 * For really structural amendments (renumbering articles), this is a
 * best-effort view; lawyers should still cross-check upstream.
 */
class DiffService
{
    /**
     * @return array{
     *   versions: array<int, int>,
     *   left: int,
     *   right: int,
     *   rows: array<int, array{position:int, status:string, left:?string, right:?string}>,
     * }
     */
    public function compare(LegalDocument $doc, ?int $left = null, ?int $right = null): array
    {
        $versions = $this->availableVersions($doc);
        if ($versions === []) {
            return ['versions' => [], 'left' => 0, 'right' => 0, 'rows' => []];
        }

        $right = $right !== null && in_array($right, $versions, true) ? $right : end($versions);
        $left = $left !== null && in_array($left, $versions, true) ? $left : (count($versions) > 1 ? $versions[count($versions) - 2] : $right);
        if ($left === $right && count($versions) > 1) {
            $left = $versions[max(0, array_search($right, $versions, true) - 1)];
        }

        $leftChunks = $this->chunksForVersion($doc, $left);
        $rightChunks = $this->chunksForVersion($doc, $right);

        $maxPos = max(
            $leftChunks->keys()->max() ?? -1,
            $rightChunks->keys()->max() ?? -1
        );

        $rows = [];
        for ($p = 0; $p <= $maxPos; $p++) {
            $l = $leftChunks->get($p)?->content;
            $r = $rightChunks->get($p)?->content;

            $status = match (true) {
                $l === null && $r !== null => 'added',
                $l !== null && $r === null => 'removed',
                $l === $r => 'unchanged',
                default => 'changed',
            };

            $rows[] = ['position' => $p, 'status' => $status, 'left' => $l, 'right' => $r];
        }

        return [
            'versions' => $versions,
            'left' => $left,
            'right' => $right,
            'rows' => $rows,
        ];
    }

    /**
     * @return array<int, int>
     */
    public function availableVersions(LegalDocument $doc): array
    {
        return LegalChunk::query()
            ->where('legal_document_id', $doc->id)
            ->select('version')
            ->distinct()
            ->orderBy('version')
            ->pluck('version')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return \Illuminate\Support\Collection<int, LegalChunk>  keyed by position
     */
    private function chunksForVersion(LegalDocument $doc, int $version): \Illuminate\Support\Collection
    {
        return LegalChunk::query()
            ->where('legal_document_id', $doc->id)
            ->where('version', $version)
            ->orderBy('position')
            ->get()
            ->keyBy('position');
    }
}
