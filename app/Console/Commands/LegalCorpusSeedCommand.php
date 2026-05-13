<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\LegalChunk;
use App\Models\LegalDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds the LegalDocument + LegalChunk tables from a curated catalog of
 * real MENA statutes (database/data/legal_catalog.php).
 *
 * Idempotent: keyed on (source, source_ref). Re-running updates content
 * in place rather than duplicating rows.
 *
 * Usage:
 *   php artisan legal:seed-catalog
 *   php artisan legal:seed-catalog --fresh    # drop existing rows first
 *   php artisan legal:seed-catalog --dry-run  # show what would be inserted
 */
class LegalCorpusSeedCommand extends Command
{
    protected $signature = 'legal:seed-catalog
                            {--fresh : Wipe existing seeded rows before inserting}
                            {--dry-run : Report what would happen without writing}';

    protected $description = 'Seed the legal corpus with curated statutes from database/data/legal_catalog.php';

    public function handle(): int
    {
        $catalogPath = database_path('data/legal_catalog.php');
        if (! is_file($catalogPath)) {
            $this->error("Catalog file not found: {$catalogPath}");

            return self::FAILURE;
        }
        $catalog = require $catalogPath;
        if (! is_array($catalog)) {
            $this->error('Catalog must return an array of statute entries.');

            return self::FAILURE;
        }

        // Practical clauses bank — same shape as the statute catalog, but
        // sourced separately so we can grow it (and de-rank it in retrieval)
        // independently of statutory sources.
        $clausesPath = database_path('data/legal_clauses.php');
        $clausesCount = 0;
        if (is_file($clausesPath)) {
            $clauses = require $clausesPath;
            if (is_array($clauses)) {
                $clausesCount = count($clauses);
                $catalog = array_merge($catalog, $clauses);
            } else {
                $this->warn('Clauses bank file found but did not return an array — skipping.');
            }
        }

        $this->info(sprintf(
            'Loaded catalog: %d statutes + %d clause-bank entries (%d total)',
            count($catalog) - $clausesCount,
            $clausesCount,
            count($catalog),
        ));

        if ($this->option('fresh')) {
            if (! $this->confirm('--fresh wipes all rows where metadata->seeded = true. Continue?', false)) {
                return self::SUCCESS;
            }
            $deleted = LegalDocument::query()
                ->whereJsonContains('metadata->seeded', true)
                ->delete();
            $this->warn("Removed {$deleted} previously-seeded documents (chunks cascade).");
        }

        $isDryRun = (bool) $this->option('dry-run');
        $created = 0;
        $updated = 0;
        $chunkCount = 0;
        $bySource = [];
        $byJurisdiction = [];

        $bar = $this->output->createProgressBar(count($catalog));
        $bar->start();

        DB::transaction(function () use ($catalog, $isDryRun, &$created, &$updated, &$chunkCount, &$bySource, &$byJurisdiction, $bar) {
            foreach ($catalog as $entry) {
                $sourceRef = $this->buildSourceRef($entry);
                $title = $entry['title_ar'] ?? $entry['title_en'] ?? $sourceRef;
                $articleBlocks = $this->articleBlocks($entry);
                $fullContent = implode("\n\n---\n\n", array_map(
                    fn ($a) => $a['heading'].": \n".$a['text'],
                    $articleBlocks
                ));
                $contentHash = hash('sha256', $title.'|'.$fullContent);

                if ($isDryRun) {
                    $bySource[$entry['source']] = ($bySource[$entry['source']] ?? 0) + 1;
                    $byJurisdiction[$entry['jurisdiction']] = ($byJurisdiction[$entry['jurisdiction']] ?? 0) + 1;
                    $created++;
                    $chunkCount += count($articleBlocks);
                    $bar->advance();

                    continue;
                }

                $existing = LegalDocument::query()
                    ->where('source', $entry['source'])
                    ->where('source_ref', $sourceRef)
                    ->first();

                $payload = [
                    'user_id' => null,
                    'title' => $title,
                    'source' => $entry['source'],
                    'source_ref' => $sourceRef,
                    'jurisdiction' => $entry['jurisdiction'],
                    'category' => $entry['category'] ?? null,
                    'tags' => $entry['tags'] ?? null,
                    'language' => $entry['language'] ?? 'ar',
                    'metadata' => [
                        'seeded' => true,
                        'title_en' => $entry['title_en'] ?? null,
                        'title_ar' => $entry['title_ar'] ?? null,
                        'citation_en' => $entry['citation_en'] ?? null,
                        'citation_ar' => $entry['citation_ar'] ?? null,
                        'article_count' => count($articleBlocks),
                    ],
                    'content' => $fullContent,
                    'chunk_count' => count($articleBlocks),
                    'ingested_at' => Carbon::now(),
                    'content_hash' => $contentHash,
                    'snippet_hash' => $contentHash,
                    'last_verified_at' => Carbon::now(),
                    'next_check_at' => Carbon::now()->addMonths(6),
                    'refresh_policy' => 'standard',
                    'version' => $existing?->version ?? 1,
                ];

                if ($existing && $existing->content_hash === $contentHash) {
                    $bar->advance();

                    continue;
                }

                if ($existing) {
                    $payload['version'] = ($existing->version ?? 1) + 1;
                    $existing->update($payload);
                    $existing->chunks()->delete();
                    $doc = $existing->fresh();
                    $updated++;
                } else {
                    $doc = LegalDocument::create($payload);
                    $created++;
                }

                foreach ($articleBlocks as $i => $a) {
                    LegalChunk::create([
                        'legal_document_id' => $doc->id,
                        'position' => $i,
                        'content' => $a['heading']."\n\n".$a['text'],
                    ]);
                    $chunkCount++;
                }

                $bySource[$entry['source']] = ($bySource[$entry['source']] ?? 0) + 1;
                $byJurisdiction[$entry['jurisdiction']] = ($byJurisdiction[$entry['jurisdiction']] ?? 0) + 1;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $verb = $isDryRun ? 'would be' : 'were';
        $this->info('Seed complete:');
        $this->line("  • {$created} documents {$verb} created");
        $this->line("  • {$updated} documents updated");
        $this->line("  • {$chunkCount} chunks {$verb} written");
        $this->newLine();

        $this->line('By source:');
        foreach ($bySource as $src => $n) {
            $label = config("legal_sources.sources.{$src}.label_en", $src);
            $this->line(sprintf('  %-22s %4d  (%s)', $src, $n, $label));
        }
        $this->newLine();

        $this->line('By jurisdiction:');
        ksort($byJurisdiction);
        foreach ($byJurisdiction as $iso => $n) {
            $this->line(sprintf('  %-3s  %d', $iso, $n));
        }
        $this->newLine();

        if (! $isDryRun) {
            $total = LegalDocument::count();
            $totalChunks = LegalChunk::count();
            $this->line('Total in DB now:');
            $this->line("  • legal_documents: {$total}");
            $this->line("  • legal_chunks:    {$totalChunks}");
        }

        return self::SUCCESS;
    }

    /**
     * Stable, deterministic source_ref so re-runs upsert correctly. We hash
     * the citation form rather than relying on positional indexes — the
     * catalog can be reordered without breaking idempotence.
     *
     * @param  array<string,mixed>  $entry
     */
    private function buildSourceRef(array $entry): string
    {
        $key = strtolower(($entry['jurisdiction'] ?? '').'|'.($entry['citation_en'] ?? $entry['title_en'] ?? ''));

        return (string) Str::of($key)
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->limit(180, '');
    }

    /**
     * Normalize each article entry into { heading, text } pairs that we use
     * to generate (a) the document content blob and (b) one chunk per
     * article. We prefer Arabic text where present (canonical wording),
     * falling back to the English structural summary.
     *
     * @param  array<string,mixed>  $entry
     * @return array<int, array{heading:string, text:string}>
     */
    private function articleBlocks(array $entry): array
    {
        $blocks = [];
        foreach (($entry['articles'] ?? []) as $a) {
            $headingParts = array_filter([
                isset($a['num']) ? 'Article '.$a['num'] : null,
                $a['heading_en'] ?? null,
            ]);
            $heading = implode(' — ', $headingParts);

            $arabic = trim((string) ($a['text_ar'] ?? ''));
            $english = trim((string) ($a['text_en'] ?? ''));

            // Combine bilingual text in chunks so RAG retrieves both
            // languages with one hit. Arabic first if present.
            $combined = trim($arabic !== '' && $english !== ''
                ? $arabic."\n\n".$english
                : ($arabic !== '' ? $arabic : $english));

            if ($combined === '' || $heading === '') {
                continue;
            }

            $blocks[] = ['heading' => $heading, 'text' => $combined];
        }

        return $blocks;
    }
}
