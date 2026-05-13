<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\LegalDocument;
use App\Services\Ingestion\FreshnessService;
use Illuminate\Console\Command;

/**
 * Kill-switch command: forces an immediate re-verification of one or more
 * documents. Use when news breaks of a major amendment before scheduled
 * detection picks it up.
 *
 *   eastlaws:invalidate --id=42                    # one doc
 *   eastlaws:invalidate --query="civil procedure"  # title LIKE match
 *   eastlaws:invalidate --all                      # mark every eastlaws doc stale
 */
class EastlawsInvalidateCommand extends Command
{
    protected $signature = 'eastlaws:invalidate
        {--id= : Specific LegalDocument ID}
        {--query= : Match documents whose title contains this substring}
        {--all : Mark every eastlaws-sourced document stale}';

    protected $description = 'Force scheduled re-verification of eastlaws documents (kill switch).';

    public function handle(FreshnessService $freshness): int
    {
        $id = $this->option('id');
        $query = $this->option('query');
        $all = (bool) $this->option('all');

        if (! $id && ! $query && ! $all) {
            $this->error('Provide one of --id, --query, or --all.');

            return self::FAILURE;
        }

        $builder = LegalDocument::query()->where('source', 'eastlaws');

        if ($id) {
            $builder->where('id', (int) $id);
        }
        if ($query) {
            $builder->where('title', 'like', '%'.$query.'%');
        }

        $docs = $builder->get();
        if ($docs->isEmpty()) {
            $this->warn('No matching eastlaws documents found.');

            return self::SUCCESS;
        }

        foreach ($docs as $doc) {
            $freshness->invalidate($doc);
            $this->line('  invalidated #'.$doc->id.': '.mb_substr((string) $doc->title, 0, 80));
        }

        $this->info('Invalidated '.$docs->count().' document(s). Next eastlaws:refresh-stale run will re-verify them.');

        return self::SUCCESS;
    }
}
