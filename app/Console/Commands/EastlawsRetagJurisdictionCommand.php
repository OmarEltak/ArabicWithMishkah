<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\LegalDocument;
use Illuminate\Console\Command;

/**
 * One-shot retroactive fixer for the eastlaws-jurisdiction bug.
 *
 * Before the bug fix in EastlawsIngestService, every document fetched via
 * `fetchAndIngestOne` was hardcoded with `jurisdiction='EG'`. So docs that
 * actually came from SA / AE / KW / QA searches got mis-tagged.
 *
 * This command infers the correct jurisdiction from the document title using
 * pattern markers that are reliable for eastlaws' Arabic legal-text titles:
 *
 *   - "اتحادي" / "الإمارات"                  → AE
 *   - title starts with "نظام " (Saudi style) → SA
 *   - "قطر"                                  → QA
 *   - "البحرين"                              → BH
 *   - "الكويت"                               → KW
 *   - "عمان"                                 → OM
 *   - "الأردن" / "هاشمية"                    → JO
 *   - "اللبنانية"                            → LB
 *   - everything else                        → EG (most laws don't
 *                                                  carry a country marker
 *                                                  and EG was the original
 *                                                  default)
 *
 * Run with --dry-run to preview the inferred labels without writing.
 */
class EastlawsRetagJurisdictionCommand extends Command
{
    protected $signature = 'eastlaws:retag-jurisdictions
        {--dry-run : Print the inferred labels without writing to the DB.}';

    protected $description = 'Repair the jurisdiction column on eastlaws docs that were tagged EG due to a hardcoding bug.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $docs = LegalDocument::query()->where('source', 'eastlaws')->get();
        $this->info('Inspecting '.$docs->count().' eastlaws docs.');

        $tally = [];
        $changed = 0;
        $unchanged = 0;

        foreach ($docs as $doc) {
            $oldJ = $doc->jurisdiction ?? 'EG';
            $newJ = self::inferJurisdictionFromTitle((string) $doc->title);

            $tally[$newJ] = ($tally[$newJ] ?? 0) + 1;

            if ($oldJ !== $newJ) {
                $this->line(sprintf(
                    '  #%d  <fg=red>%s</> → <fg=green>%s</>  %s',
                    $doc->id,
                    $oldJ,
                    $newJ,
                    mb_substr($doc->title, 0, 70)
                ));
                if (! $dryRun) {
                    $doc->forceFill(['jurisdiction' => $newJ])->save();
                }
                $changed++;
            } else {
                $unchanged++;
            }
        }

        $this->newLine();
        $this->info('Summary:');
        $this->line('  Changed: '.$changed);
        $this->line('  Unchanged: '.$unchanged);
        $this->line('  Inferred breakdown by jurisdiction:');
        ksort($tally);
        foreach ($tally as $j => $n) {
            $this->line('    '.$j.': '.$n);
        }
        if ($dryRun) {
            $this->warn('--dry-run: nothing was written. Re-run without the flag to apply.');
        }

        return self::SUCCESS;
    }

    /**
     * Returns an ISO code best matching the document title's country
     * fingerprint. Falls back to EG for ambiguous Arabic-only titles.
     */
    public static function inferJurisdictionFromTitle(string $title): string
    {
        $t = trim($title);

        // UAE federal laws are almost universally tagged with "اتحادي" or
        // "الإمارات". Strong signal.
        if (str_contains($t, 'اتحادي') || str_contains($t, 'الإمارات')) {
            return 'AE';
        }

        // Saudi statutes use "نظام" prefix instead of "قانون". Royal
        // Decree numbers also include "م/" prefix.
        if (preg_match('/^(?:نظام|مرسوم ملكي|الأمر السامي)/u', $t) || str_contains($t, 'م/') || str_contains($t, 'السعودية')) {
            return 'SA';
        }

        if (str_contains($t, 'قطر') && ! str_contains($t, 'قطرة')) {
            return 'QA';
        }
        if (str_contains($t, 'البحرين') || str_contains($t, 'مملكة البحرين')) {
            return 'BH';
        }
        if (str_contains($t, 'الكويت')) {
            return 'KW';
        }
        if (str_contains($t, 'سلطنة عمان')) {
            return 'OM';
        }
        if (str_contains($t, 'الأردن') || str_contains($t, 'الهاشمية')) {
            return 'JO';
        }
        if (str_contains($t, 'اللبنانية') || str_contains($t, 'لبنان')) {
            return 'LB';
        }

        // Default to EG. The vast majority of eastlaws Egyptian laws carry
        // no country marker in their title — they're just "قانون رقم X لسنة Y".
        return 'EG';
    }
}
