<?php

declare(strict_types=1);

namespace App\Services\Contracts;

/**
 * Paragraph-level diff for contract bodies. Splits both old and new bodies
 * on blank lines, computes the longest common subsequence (LCS), and emits
 * a sequence of {add, remove, context} rows suitable for a unified-diff
 * style display.
 *
 * Why paragraph-level (not line-level): Arabic legal contracts wrap
 * differently in different editors, so per-line diffs are noisy. Paragraphs
 * are the unit of legal meaning — articles, signature blocks, clauses.
 *
 * Why LCS (not Myers): the bodies are <100 paragraphs so O(N·M) is fine,
 * and LCS produces stable, intuitive output for the kind of mid-clause
 * edits lawyers make. Myers would be marginally faster but harder to read.
 */
class ContractDiffer
{
    /**
     * @return array{
     *   added: int,
     *   removed: int,
     *   unchanged: int,
     *   rows: array<int, array{type: 'add'|'remove'|'context', text: string}>
     * }
     */
    public function diff(string $oldBody, string $newBody): array
    {
        $oldParas = self::splitParagraphs($oldBody);
        $newParas = self::splitParagraphs($newBody);

        $lcs = self::lcs($oldParas, $newParas);

        $rows = [];
        $i = 0;
        $j = 0;
        $k = 0;
        $added = 0;
        $removed = 0;
        $unchanged = 0;

        while ($i < count($oldParas) || $j < count($newParas)) {
            // Both sides on an LCS match — context line
            if (
                $k < count($lcs)
                && $i < count($oldParas)
                && $j < count($newParas)
                && $oldParas[$i] === $lcs[$k]
                && $newParas[$j] === $lcs[$k]
            ) {
                $rows[] = ['type' => 'context', 'text' => $oldParas[$i]];
                $i++;
                $j++;
                $k++;
                $unchanged++;

                continue;
            }
            // Old paragraph not in LCS at this position → removed
            if ($i < count($oldParas) && ($k >= count($lcs) || $oldParas[$i] !== $lcs[$k])) {
                $rows[] = ['type' => 'remove', 'text' => $oldParas[$i]];
                $i++;
                $removed++;

                continue;
            }
            // New paragraph not in LCS → added
            if ($j < count($newParas)) {
                $rows[] = ['type' => 'add', 'text' => $newParas[$j]];
                $j++;
                $added++;
            }
        }

        return [
            'added' => $added,
            'removed' => $removed,
            'unchanged' => $unchanged,
            'rows' => $rows,
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function splitParagraphs(string $body): array
    {
        $parts = preg_split('/\n{2,}/u', trim($body)) ?: [];
        $out = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '') {
                $out[] = $p;
            }
        }

        return $out;
    }

    /**
     * Standard dynamic-programming LCS. Returns the longest common subsequence
     * of paragraphs (preserving order). At ~100×100 paragraphs this is ~10K
     * cells — instant.
     *
     * @param  array<int, string>  $a
     * @param  array<int, string>  $b
     * @return array<int, string>
     */
    private static function lcs(array $a, array $b): array
    {
        $m = count($a);
        $n = count($b);
        if ($m === 0 || $n === 0) {
            return [];
        }
        $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0));
        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($a[$i - 1] === $b[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1] + 1;
                } else {
                    $dp[$i][$j] = max($dp[$i - 1][$j], $dp[$i][$j - 1]);
                }
            }
        }
        $lcs = [];
        $i = $m;
        $j = $n;
        while ($i > 0 && $j > 0) {
            if ($a[$i - 1] === $b[$j - 1]) {
                array_unshift($lcs, $a[$i - 1]);
                $i--;
                $j--;
            } elseif ($dp[$i - 1][$j] >= $dp[$i][$j - 1]) {
                $i--;
            } else {
                $j--;
            }
        }

        return $lcs;
    }
}
