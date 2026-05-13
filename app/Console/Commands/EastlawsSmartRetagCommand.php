<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\LegalDocument;
use App\Services\Ingestion\EastlawsClient;
use App\Services\Ingestion\EastlawsIngestService;
use Illuminate\Console\Command;

/**
 * Authoritative jurisdiction repair for eastlaws docs.
 *
 * Title-based heuristics work for ~80% of cases (UAE federal laws have
 * "اتحادي", Saudi laws use "نظام" prefix, Kuwaiti laws often mention
 * "الكويت") but fail for Qatari laws — most Qatari titles are just
 * "قانون رقم X لسنة Y" with no country marker.
 *
 * This command sidesteps the heuristic by going back to the source: it
 * re-runs every search query in the snapshot config (per its declared
 * jurisdiction) and tags any LegalDocument whose eastlaws_id matches
 * the search result with the correct ISO code.
 *
 * Throttled at 3s/request via EastlawsClient. ~5 queries × 5 jurisdictions
 * × 2 pages ≈ 50 requests = ~3 minutes wall-clock.
 *
 *   php artisan eastlaws:smart-retag
 *   php artisan eastlaws:smart-retag --jurisdiction=QA   # only QA
 *   php artisan eastlaws:smart-retag --dry-run
 */
class EastlawsSmartRetagCommand extends Command
{
    protected $signature = 'eastlaws:smart-retag
        {--jurisdiction= : Limit to one jurisdiction code (e.g. QA, AE).}
        {--profile= : Limit to one query profile (general / corporate). Default: walk both.}
        {--dry-run : Print proposed re-tags without writing.}';

    protected $description = 'Bulletproof jurisdiction + category tag repair by re-querying eastlaws for each snapshot query.';

    public function handle(EastlawsIngestService $svc, EastlawsClient $client): int
    {
        if (! $svc->isEnabled()) {
            $this->error('Eastlaws integration disabled.');

            return self::FAILURE;
        }

        $matrix = config('eastlaws_snapshot.jurisdictions', []);
        $filter = $this->option('jurisdiction');
        $profileFilter = $this->option('profile');
        $dryRun = (bool) $this->option('dry-run');

        if ($filter) {
            $filter = strtoupper($filter);
            if (! isset($matrix[$filter])) {
                $this->error("Unknown jurisdiction '{$filter}'.");

                return self::FAILURE;
            }
            $matrix = [$filter => $matrix[$filter]];
        }

        $maxPages = (int) config('eastlaws_snapshot.max_pages_per_query', 2);
        $stats = ['retagged_jurisdiction' => 0, 'set_category' => 0, 'confirmed' => 0, 'missing' => 0];

        foreach ($matrix as $jurisdiction => $cfg) {
            $countryId = (int) ($cfg['country_id'] ?? 0);

            // Walk every profile (general + corporate + ...) so we cover
            // the full corpus in one pass. Or restrict to a single profile.
            $profileMap = is_array($cfg['queries'] ?? null) ? $cfg['queries'] : [];
            $isStructured = $profileMap !== [] && array_keys($profileMap) === array_filter(array_keys($profileMap), 'is_string');
            $profiles = $isStructured ? $profileMap : ['__legacy__' => $profileMap];

            $this->line("<fg=cyan>━━ {$jurisdiction} (country_id={$countryId}) ━━</>");

            foreach ($profiles as $profileName => $entries) {
                if ($profileFilter && $profileName !== $profileFilter && $profileName !== '__legacy__') {
                    continue;
                }
                if (! is_array($entries)) {
                    continue;
                }

                if ($profileName !== '__legacy__') {
                    $this->line("  <fg=blue>profile: {$profileName}</>");
                }

                foreach ($entries as $entry) {
                    $q = is_string($entry) ? $entry : (string) ($entry['q'] ?? '');
                    $category = is_array($entry) ? ($entry['category'] ?? null) : null;
                    if ($q === '') {
                        continue;
                    }
                    $this->line(sprintf('    • %s   <fg=magenta>[%s]</>', $q, $category ?: 'no-category'));

                    $idsFound = [];
                    for ($page = 1; $page <= $maxPages; $page++) {
                        try {
                            $html = $client->searchLegislation($q, $countryId, $page);
                            $refs = EastlawsClient::parseResults($html);
                            if ($refs === []) {
                                break;
                            }
                            foreach ($refs as $ref) {
                                $idsFound[] = ['recId' => $ref['id'], 'recType' => $ref['recType']];
                            }
                        } catch (\Throwable $e) {
                            $this->line('      <fg=red>search failed:</> '.$e->getMessage());
                            break;
                        }
                    }

                    $localJurisdiction = 0;
                    $localCategory = 0;
                    $localConfirmed = 0;
                    $localMissing = 0;
                    foreach ($idsFound as $ref) {
                        $sourceRef = $ref['recType'].':'.$ref['recId'];
                        $doc = LegalDocument::query()
                            ->where('source', 'eastlaws')
                            ->where('source_ref', $sourceRef)
                            ->first();
                        if (! $doc) {
                            $localMissing++;

                            continue;
                        }

                        $updates = [];
                        if ($doc->jurisdiction !== $jurisdiction) {
                            $updates['jurisdiction'] = $jurisdiction;
                            $localJurisdiction++;
                        }
                        // Only assign category when the doc has none yet.
                        // Don't overwrite — a doc tagged via 'general'
                        // (e.g. labour) shouldn't get retagged 'tax' just
                        // because a corporate-profile query happened to
                        // surface the same eastlaws id.
                        if ($category && empty($doc->category)) {
                            $updates['category'] = $category;
                            $localCategory++;
                        }
                        if ($updates === []) {
                            $localConfirmed++;

                            continue;
                        }
                        if (! $dryRun) {
                            $meta = is_array($doc->metadata) ? $doc->metadata : [];
                            $meta['eastlaws_country_id'] = $countryId;
                            $updates['metadata'] = $meta;
                            $doc->forceFill($updates)->save();
                        }
                    }

                    $this->line(sprintf(
                        '      found %d · <fg=green>confirmed %d</> · <fg=yellow>set jurisdiction %d</> · <fg=cyan>set category %d</> · missing %d',
                        count($idsFound), $localConfirmed, $localJurisdiction, $localCategory, $localMissing
                    ));
                    $stats['retagged_jurisdiction'] += $localJurisdiction;
                    $stats['set_category'] += $localCategory;
                    $stats['confirmed'] += $localConfirmed;
                    $stats['missing'] += $localMissing;
                }
            }
            $this->newLine();
        }

        $this->info('Smart retag summary:');
        $this->line('  Jurisdictions corrected: '.$stats['retagged_jurisdiction']);
        $this->line('  Categories set: '.$stats['set_category']);
        $this->line('  Already correct: '.$stats['confirmed']);
        $this->line('  In eastlaws but missing from local DB: '.$stats['missing']);
        if ($dryRun) {
            $this->warn('--dry-run: nothing was written.');
        }

        return self::SUCCESS;
    }
}
