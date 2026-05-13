<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\VerifyDocumentFreshnessJob;
use App\Models\LegalDocument;
use App\Services\Ingestion\FreshnessService;
use Illuminate\Console\Command;

/**
 * Picks up to N eastlaws-sourced documents whose next_check_at has elapsed
 * (or never been set) and verifies their content against eastlaws. Designed
 * to run nightly via the scheduler — see routes/console.php.
 *
 * Synchronous mode is the default for visibility during operator runs;
 * --queue dispatches each verification as a queued job for production.
 */
class EastlawsRefreshStaleCommand extends Command
{
    protected $signature = 'eastlaws:refresh-stale
        {--limit=20 : Max documents to verify in one run}
        {--queue : Dispatch verifications to the queue instead of running inline}';

    protected $description = 'Re-verify stale eastlaws documents and supersede chunks when content has changed.';

    public function handle(FreshnessService $freshness): int
    {
        if (! $freshness->isEnabled()) {
            $this->error('Eastlaws integration is disabled. Nothing to refresh.');

            return self::FAILURE;
        }

        $limit = max(1, min((int) $this->option('limit'), 200));
        $useQueue = (bool) $this->option('queue');

        $stale = LegalDocument::query()
            ->where('source', 'eastlaws')
            ->where(function ($q): void {
                $q->whereNull('next_check_at')
                    ->orWhere('next_check_at', '<=', now());
            })
            ->orderByRaw('next_check_at IS NULL DESC') // never-checked first
            ->orderBy('next_check_at')
            ->limit($limit)
            ->get();

        if ($stale->isEmpty()) {
            $this->info('No stale documents. Everything within freshness window.');

            return self::SUCCESS;
        }

        $this->info("Found {$stale->count()} stale document(s). Mode: ".($useQueue ? 'queue' : 'inline'));
        $tally = ['unchanged' => 0, 'updated' => 0, 'failed' => 0, 'skipped' => 0, 'queued' => 0];

        foreach ($stale as $doc) {
            if ($useQueue) {
                VerifyDocumentFreshnessJob::dispatch($doc->id);
                $tally['queued']++;

                continue;
            }

            $this->line("  ".$doc->id.': '.mb_substr((string) $doc->title, 0, 80));
            $outcome = $freshness->verify($doc);
            $tally[$outcome] = ($tally[$outcome] ?? 0) + 1;
            $this->line('    → '.$outcome);
        }

        $this->newLine();
        $this->info('Summary: '.json_encode($tally, JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
