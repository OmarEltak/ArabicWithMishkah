<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\LegalChunk;
use App\Services\AI\EmbeddingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Backfill embeddings for legal_chunks that don't have them yet.
 *
 * Used in two scenarios:
 *
 *   1) After `php artisan legal:seed-catalog` — newly-seeded chunks are
 *      written without embeddings to avoid coupling the seed to an upstream
 *      embedding API. This command fills them in.
 *
 *   2) After switching embedding providers (e.g. setting OPENAI_API_KEY to
 *      promote from `mock:hash` to real OpenAI vectors). Pass --force to
 *      re-embed everything; default behavior only touches NULL rows.
 *
 * Batches in groups of 64 to amortize the per-request HTTP overhead of
 * upstream providers. Failures on a batch don't halt the run — the batch
 * is logged and skipped, so a single bad chunk can't poison the whole job.
 *
 * Usage:
 *   php artisan legal:embed-backfill                 # only NULL rows
 *   php artisan legal:embed-backfill --force         # all rows (re-embed)
 *   php artisan legal:embed-backfill --batch=32      # smaller batch
 *   php artisan legal:embed-backfill --dry-run       # what would happen
 */
class LegalEmbedBackfillCommand extends Command
{
    protected $signature = 'legal:embed-backfill
                            {--force : Re-embed chunks that already have vectors}
                            {--batch=64 : Batch size for the embedding API call}
                            {--dry-run : Show what would be embedded without writing}
                            {--limit= : Cap total rows processed (debugging)}';

    protected $description = 'Backfill embeddings for legal_chunks that have no embedding (or all with --force)';

    public function handle(EmbeddingService $embed): int
    {
        $batch = max(1, (int) $this->option('batch'));
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') === null ? null : max(1, (int) $this->option('limit'));

        $modelName = $embed->modelName();
        $configured = $embed->isConfigured();

        $this->line('Embedding provider: <fg=cyan>'.$modelName.'</> ('.($configured ? 'real' : 'mock fallback').')');
        if (! $configured) {
            $this->warn('No real embedding provider configured. Using deterministic mock vectors.');
            $this->warn('Set OPENAI_API_KEY or VOYAGE_API_KEY in .env and re-run with --force for production-quality retrieval.');
        }

        $query = LegalChunk::query();
        if (! $force) {
            $query->whereNull('embedding');
        }
        if ($limit !== null) {
            $query->limit($limit);
        }
        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('Nothing to embed. (Use --force to re-embed existing rows.)');
            return self::SUCCESS;
        }

        $this->info(sprintf('Embedding %s chunks in batches of %d…', number_format($total), $batch));

        if ($dryRun) {
            $this->line('Dry run: no writes performed.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;
        $errors = 0;

        $query->select(['id', 'content'])->orderBy('id')->chunkById($batch, function ($chunks) use ($embed, $bar, &$processed, &$errors, $modelName, $batch) {
            $inputs = $chunks->pluck('content')->map(fn ($c) => (string) $c)->all();

            try {
                $vectors = $embed->embedBatch($inputs);
            } catch (\Throwable $e) {
                $errors += $chunks->count();
                Log::channel('errors')->error('Embedding batch failed', [
                    'error' => $e->getMessage(),
                    'chunk_ids' => $chunks->pluck('id')->all(),
                ]);
                $bar->advance($chunks->count());
                return;
            }

            foreach ($chunks as $i => $chunk) {
                $vec = $vectors[$i] ?? null;
                if (! is_array($vec) || $vec === []) {
                    $errors++;
                    continue;
                }
                $chunk->update([
                    'embedding' => $vec,
                    'embedding_dim' => count($vec),
                    'embedding_model' => $modelName,
                ]);
                $processed++;
            }

            $bar->advance($chunks->count());
        });

        $bar->finish();
        $this->newLine(2);

        $this->info('Backfill complete:');
        $this->line('  • '.number_format($processed).' chunks embedded');
        if ($errors > 0) {
            $this->warn('  • '.number_format($errors).' errors (see storage/logs/errors.log)');
        }

        $remaining = LegalChunk::whereNull('embedding')->count();
        $this->line('  • '.number_format($remaining).' chunks still without an embedding');

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
