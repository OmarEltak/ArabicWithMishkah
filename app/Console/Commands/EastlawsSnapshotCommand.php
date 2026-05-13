<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\IngestEastlawsQueryJob;
use App\Models\LegalDocument;
use App\Services\Ingestion\EastlawsIngestService;
use Illuminate\Console\Command;

/**
 * Bulk snapshot — walks the keyword × jurisdiction matrix in
 * config/eastlaws_snapshot.php and ingests the most-cited statutes for
 * every configured country.
 *
 * Designed for the "moat" use case: build a permanent local corpus of legal
 * authority while the eastlaws subscription is active, so that even if
 * access is revoked tomorrow the system retains its retrieval+citation
 * value indefinitely.
 *
 * Idempotent: re-running skips docs already in legal_documents
 * (source='eastlaws', matching source_ref). Throttled at 3s/request via
 * EastlawsClient. Total wall-clock for a fresh full snapshot is roughly
 * (queries × (max_docs + 1) × 3s) ≈ 15-30 minutes.
 *
 *   php artisan eastlaws:snapshot                 # full sweep, inline
 *   php artisan eastlaws:snapshot --jurisdiction=EG
 *   php artisan eastlaws:snapshot --queue          # dispatch jobs to queue
 *   php artisan eastlaws:snapshot --dry-run        # preview only, no fetch
 *   php artisan eastlaws:snapshot --max=20 --pages=3
 */
class EastlawsSnapshotCommand extends Command
{
    protected $signature = 'eastlaws:snapshot
        {--jurisdiction= : Filter to one jurisdiction code (e.g. EG, SA, AE). Default: all configured.}
        {--profile=general : Which query set to run — "general" (broad sweep) or "corporate" (M&A / capital markets / arbitration).}
        {--max= : Override max documents per query (default from config).}
        {--pages= : Override max pages per query (default from config).}
        {--queue : Dispatch IngestEastlawsQueryJob to the queue per query instead of running inline.}
        {--dry-run : Print the planned queries without fetching anything.}';

    protected $description = 'Bulk-snapshot eastlaws into the local KB across jurisdictions — defensive moat-building.';

    public function handle(EastlawsIngestService $svc): int
    {
        $matrix = config('eastlaws_snapshot.jurisdictions', []);
        $defaultMax = (int) config('eastlaws_snapshot.max_documents_per_query', 10);
        $defaultPages = (int) config('eastlaws_snapshot.max_pages_per_query', 2);

        $filter = $this->option('jurisdiction');
        $profile = (string) ($this->option('profile') ?: 'general');
        $max = (int) ($this->option('max') ?: $defaultMax);
        $pages = (int) ($this->option('pages') ?: $defaultPages);
        $useQueue = (bool) $this->option('queue');
        $dryRun = (bool) $this->option('dry-run');

        if ($filter) {
            $filter = strtoupper($filter);
            if (! isset($matrix[$filter])) {
                $this->error("Jurisdiction '{$filter}' not in config/eastlaws_snapshot.php. Available: ".implode(', ', array_keys($matrix)));

                return self::FAILURE;
            }
            $matrix = [$filter => $matrix[$filter]];
        }

        if (! $dryRun && ! $svc->isEnabled()) {
            $this->error('Eastlaws integration is disabled (set EASTLAWS_ENABLED=true and credentials).');

            return self::FAILURE;
        }

        // Plan summary — every run prints what it's about to do.
        // Queries can live under 'queries' (legacy flat) or
        // 'queries.{profile}' (new structured form). Normalise to a flat
        // array of {q, category, tags} per jurisdiction.
        foreach ($matrix as $j => &$cfg) {
            $cfg['_queries_resolved'] = self::resolveQueries($cfg, $profile);
        }
        unset($cfg);

        $totalQueries = 0;
        foreach ($matrix as $cfg) {
            $totalQueries += count($cfg['_queries_resolved']);
        }
        $estimatedRequests = $totalQueries * ($max + 1); // 1 search + max fetch per query
        $estimatedSeconds = $estimatedRequests * 3;
        $estMin = (int) ceil($estimatedSeconds / 60);

        $this->info(sprintf(
            'Snapshot plan: %d jurisdiction(s), profile=%s, %d total queries, max=%d docs / pages=%d each.',
            count($matrix),
            $profile,
            $totalQueries,
            $max,
            $pages
        ));
        $this->info(sprintf(
            'Estimated wall-clock at 3s/request throttle: ~%d minutes (%d HTTP calls).',
            $estMin,
            $estimatedRequests
        ));
        if ($dryRun) {
            $this->warn('--dry-run: no requests will be made.');
        }
        if ($useQueue) {
            $this->info('--queue: dispatching jobs; this command returns immediately and the queue worker handles fetching.');
        }
        $this->newLine();

        $tally = ['queries' => 0, 'ingested' => 0, 'skipped' => 0, 'errors' => 0, 'queued' => 0];
        $beforeCount = LegalDocument::query()->where('source', 'eastlaws')->count();

        foreach ($matrix as $jurisdiction => $cfg) {
            $countryId = (int) ($cfg['country_id'] ?? 0);
            $queries = $cfg['_queries_resolved'];

            $this->line("<fg=cyan>━━ {$jurisdiction} (country_id={$countryId}) ━━</>");

            foreach ($queries as $row) {
                $q = (string) $row['q'];
                $category = $row['category'] ?? null;
                $tags = is_array($row['tags'] ?? null) ? $row['tags'] : [];
                $tally['queries']++;
                $this->line(sprintf('  • %s   <fg=magenta>[%s]</>', $q, $category ?: 'no-category'));

                if ($dryRun) {
                    continue;
                }

                if ($useQueue) {
                    IngestEastlawsQueryJob::dispatch(null, $q, $countryId, $max, $pages);
                    $tally['queued']++;
                    $this->line('    <fg=blue>queued</>');

                    continue;
                }

                try {
                    $stats = $svc->bulkIngestQuery(null, $q, $countryId, $max, $pages, $category, $tags);
                    $tally['ingested'] += (int) ($stats['ingested'] ?? 0);
                    $tally['skipped'] += (int) ($stats['skipped'] ?? 0);
                    $tally['errors'] += (int) ($stats['errors'] ?? 0);
                    $this->line(sprintf(
                        '    <fg=green>ingested</> %d, <fg=yellow>skipped</> %d, <fg=red>errors</> %d',
                        $stats['ingested'] ?? 0,
                        $stats['skipped'] ?? 0,
                        $stats['errors'] ?? 0
                    ));
                } catch (\Throwable $e) {
                    $tally['errors']++;
                    $this->line('    <fg=red>FAILED:</> '.$e->getMessage());
                }
            }
            $this->newLine();
        }

        $afterCount = LegalDocument::query()->where('source', 'eastlaws')->count();
        $delta = $afterCount - $beforeCount;

        $this->info('Snapshot summary:');
        $this->line('  Queries dispatched: '.$tally['queries']);
        if ($useQueue) {
            $this->line('  Jobs queued: '.$tally['queued']);
        } elseif (! $dryRun) {
            $this->line('  New documents ingested: '.$tally['ingested']);
            $this->line('  Documents skipped (already present): '.$tally['skipped']);
            $this->line('  Errors: '.$tally['errors']);
        }
        $this->line('  Eastlaws docs in KB: '.$beforeCount.' → '.$afterCount.($delta > 0 ? " (+{$delta})" : ''));

        return self::SUCCESS;
    }

    /**
     * Normalise legacy and structured query formats into a uniform list.
     *
     * Supported config shapes:
     *   1. legacy flat:    'queries' => ['Q1', 'Q2', ...]
     *   2. structured:     'queries' => [['q' => 'Q', 'category' => 'X'], ...]
     *   3. profiled:       'queries' => ['general' => [...], 'corporate' => [...]]
     *
     * @return array<int, array{q:string, category:?string, tags:array<int,string>}>
     */
    private static function resolveQueries(array $cfg, string $profile): array
    {
        $queries = $cfg['queries'] ?? [];
        if (! is_array($queries)) {
            return [];
        }

        // Profile-keyed map?
        $isProfileMap = $queries !== [] && array_keys($queries) === array_filter(array_keys($queries), 'is_string');
        if ($isProfileMap) {
            $picked = $queries[$profile] ?? [];
        } else {
            $picked = $queries;
        }

        if (! is_array($picked)) {
            return [];
        }

        $resolved = [];
        foreach ($picked as $entry) {
            if (is_string($entry)) {
                $resolved[] = ['q' => $entry, 'category' => null, 'tags' => []];
            } elseif (is_array($entry) && isset($entry['q'])) {
                $resolved[] = [
                    'q' => (string) $entry['q'],
                    'category' => isset($entry['category']) ? (string) $entry['category'] : null,
                    'tags' => is_array($entry['tags'] ?? null) ? array_map('strval', $entry['tags']) : [],
                ];
            }
        }

        return $resolved;
    }
}
