<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Ingestion\EastlawsIngestService;
use Illuminate\Console\Command;

class EastlawsIngestCommand extends Command
{
    protected $signature = 'eastlaws:ingest
        {query : Search term (e.g. "قانون المرافعات")}
        {--country=1 : Country ID (1=Egypt, 4=UAE, 7=Qatar, 9=Saudi, …)}
        {--max=10 : Max documents to ingest (1–100)}
        {--pages=1 : Max search pages to walk (1–10)}';

    protected $description = 'Search eastlaws.com and bulk-ingest legislation into the knowledge base.';

    public function handle(EastlawsIngestService $svc): int
    {
        if (! $svc->isEnabled()) {
            $this->error('Eastlaws integration is disabled. Set EASTLAWS_ENABLED=true and credentials in .env.');

            return self::FAILURE;
        }

        $query = (string) $this->argument('query');
        $countryId = (int) $this->option('country');
        $max = (int) $this->option('max');
        $pages = (int) $this->option('pages');

        $this->info("Searching eastlaws for: {$query} (country={$countryId}, max={$max}, pages={$pages})");
        $this->warn('This is throttled to ~3s between requests; it will take a while for larger runs.');

        $stats = $svc->bulkIngestQuery(null, $query, $countryId, $max, $pages);

        $this->newLine();
        $this->info("Ingested: {$stats['ingested']}, skipped (already present): {$stats['skipped']}, errors: {$stats['errors']}");
        foreach ($stats['documents'] as $d) {
            $this->line("  #{$d['id']}  ".mb_substr($d['title'], 0, 120));
        }

        return self::SUCCESS;
    }
}
