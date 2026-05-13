<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\LegalChunk;
use App\Services\AI\RagService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-shot backfill: copies every JSON `embedding` value into the native
 * pgvector `embedding_pgv` column. Idempotent — safe to re-run after a
 * migration from SQLite/JSON to Postgres+pgvector.
 *
 *   php artisan rag:promote-to-pgvector --chunk=200
 */
class PromoteToPgVectorCommand extends Command
{
    protected $signature = 'rag:promote-to-pgvector
        {--chunk=200 : Rows per batch}
        {--missing-only : Only fill rows where embedding_pgv IS NULL}';

    protected $description = 'Backfill pgvector column from JSON embeddings (run after switching DB to Postgres).';

    public function handle(): int
    {
        if (! RagService::usingPgVector()) {
            $this->error('pgvector is not active. Confirm DB_CONNECTION=pgsql and the vector extension is installed.');

            return self::FAILURE;
        }

        $missingOnly = (bool) $this->option('missing-only');
        $batch = max(50, min(2000, (int) $this->option('chunk')));

        $query = LegalChunk::query()->whereNotNull('embedding');
        if ($missingOnly) {
            $query->whereNull('embedding_pgv');
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('Nothing to backfill.');

            return self::SUCCESS;
        }

        $this->info("Promoting {$total} chunk(s) to pgvector ".($missingOnly ? '(missing only)' : '(all)').'...');
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $promoted = 0;
        $skipped = 0;

        $query->orderBy('id')->chunk($batch, function ($chunks) use (&$promoted, &$skipped, $bar): void {
            foreach ($chunks as $chunk) {
                $vec = $chunk->embedding;
                if (! is_array($vec) || count($vec) === 0) {
                    $skipped++;
                    $bar->advance();

                    continue;
                }
                DB::statement(
                    'UPDATE legal_chunks SET embedding_pgv = ?::vector WHERE id = ?',
                    [RagService::pgvectorLiteral($vec), $chunk->id]
                );
                $promoted++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Done. Promoted: {$promoted}, skipped (no embedding): {$skipped}.");

        return self::SUCCESS;
    }
}
