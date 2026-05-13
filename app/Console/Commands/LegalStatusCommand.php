<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\LegalChunk;
use App\Models\LegalDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Reports corpus health for operations and oncall.
 *
 * Usage:
 *   php artisan legal:status
 *   php artisan legal:status --jurisdiction=EG
 *   php artisan legal:status --json
 */
class LegalStatusCommand extends Command
{
    protected $signature = 'legal:status
                            {--jurisdiction= : Filter to one jurisdiction (e.g. EG)}
                            {--json : Emit JSON instead of a human-readable report}';

    protected $description = 'Report legal corpus health: counts, freshness, source spread, coverage status';

    public function handle(): int
    {
        $jurisdiction = $this->option('jurisdiction');
        $payload = $this->buildReport($jurisdiction);

        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->renderHuman($payload);

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReport(?string $jurisdiction): array
    {
        $base = LegalDocument::query();
        if ($jurisdiction) {
            $base->where('jurisdiction', strtoupper($jurisdiction));
        }

        $total = (clone $base)->count();
        $totalChunks = LegalChunk::query()
            ->when($jurisdiction, fn ($q) => $q->whereIn('legal_document_id',
                LegalDocument::query()->where('jurisdiction', strtoupper($jurisdiction))->pluck('id')
            ))
            ->count();

        $official = (array) config('legal_sources.official_slugs', []);
        $officialCount = (clone $base)->whereIn('source', $official)->count();

        $stale = (clone $base)
            ->whereIn('source', $official)
            ->where(fn ($q) => $q->whereNull('next_check_at')->orWhere('next_check_at', '<=', now()))
            ->count();

        $bySource = (clone $base)
            ->selectRaw('source, count(*) as n')
            ->groupBy('source')
            ->orderByDesc('n')
            ->pluck('n', 'source')
            ->toArray();

        $byJurisdiction = LegalDocument::query()
            ->whereNotNull('jurisdiction')
            ->whereIn('source', $official)
            ->selectRaw('jurisdiction, count(*) as n')
            ->groupBy('jurisdiction')
            ->orderBy('jurisdiction')
            ->pluck('n', 'jurisdiction')
            ->toArray();

        $byCategory = (clone $base)
            ->whereNotNull('category')
            ->selectRaw('category, count(*) as n')
            ->groupBy('category')
            ->orderByDesc('n')
            ->pluck('n', 'category')
            ->toArray();

        $verifiedAge = (clone $base)
            ->whereNotNull('last_verified_at')
            ->orderByDesc('last_verified_at')
            ->limit(1)
            ->value('last_verified_at');

        $coverage = config('coverage.jurisdictions', []);
        $coverageBreakdown = [
            'live' => array_keys(array_filter($coverage, fn ($v) => $v === 'live')),
            'beta' => array_keys(array_filter($coverage, fn ($v) => $v === 'beta')),
            'preview' => array_keys(array_filter($coverage, fn ($v) => $v === 'preview')),
        ];

        return [
            'as_of' => Carbon::now()->toAtomString(),
            'filter_jurisdiction' => $jurisdiction ? strtoupper($jurisdiction) : null,
            'totals' => [
                'documents' => $total,
                'chunks' => $totalChunks,
                'official_documents' => $officialCount,
                'stale_official' => $stale,
                'fresh_official' => max(0, $officialCount - $stale),
                'fresh_pct' => $officialCount > 0 ? (int) round(($officialCount - $stale) / $officialCount * 100) : 100,
            ],
            'newest_verification' => $verifiedAge?->toAtomString(),
            'by_source' => $bySource,
            'by_jurisdiction' => $byJurisdiction,
            'by_category' => $byCategory,
            'coverage' => $coverageBreakdown,
        ];
    }

    /**
     * @param  array<string, mixed>  $r
     */
    private function renderHuman(array $r): void
    {
        $this->newLine();
        $this->line('<fg=cyan;options=bold>Legal corpus status</> ('.$r['as_of'].')');
        if ($r['filter_jurisdiction']) {
            $this->line('Filter: <fg=yellow>'.$r['filter_jurisdiction'].'</>');
        }
        $this->newLine();

        $t = $r['totals'];
        $this->line('Totals');
        $this->line(sprintf('  documents          %s', number_format($t['documents'])));
        $this->line(sprintf('  chunks             %s', number_format($t['chunks'])));
        $this->line(sprintf('  official sources   %s', number_format($t['official_documents'])));
        $color = $t['fresh_pct'] >= 90 ? 'green' : ($t['fresh_pct'] >= 60 ? 'yellow' : 'red');
        $this->line(sprintf('  freshness          <fg=%s>%d%%</> (%d fresh / %d stale)',
            $color, $t['fresh_pct'], $t['fresh_official'], $t['stale_official']));
        if ($r['newest_verification']) {
            $this->line(sprintf('  last verification  %s', $r['newest_verification']));
        }
        $this->newLine();

        $this->line('By source');
        foreach ($r['by_source'] as $src => $n) {
            $label = config("legal_sources.sources.{$src}.label_en", $src);
            $this->line(sprintf('  %-22s %5d   <fg=gray>%s</>', $src, $n, $label));
        }
        $this->newLine();

        $this->line('By jurisdiction (official sources only)');
        foreach ($r['by_jurisdiction'] as $iso => $n) {
            $level = config("coverage.jurisdictions.{$iso}", 'preview');
            $color = $level === 'live' ? 'green' : ($level === 'beta' ? 'yellow' : 'gray');
            $this->line(sprintf('  %-3s  %5d   <fg=%s>%s</>', $iso, $n, $color, strtoupper($level)));
        }
        $this->newLine();

        if ($r['by_category']) {
            $this->line('By category');
            foreach ($r['by_category'] as $cat => $n) {
                $this->line(sprintf('  %-18s %5d', $cat, $n));
            }
            $this->newLine();
        }

        $cov = $r['coverage'];
        $this->line('Coverage status');
        $this->line(sprintf('  <fg=green>LIVE</>     %s', implode(', ', $cov['live']) ?: '(none)'));
        $this->line(sprintf('  <fg=yellow>BETA</>     %s', implode(', ', $cov['beta']) ?: '(none)'));
        $this->line(sprintf('  <fg=gray>PREVIEW</>  %s', implode(', ', $cov['preview']) ?: '(none)'));
        $this->newLine();
    }
}
