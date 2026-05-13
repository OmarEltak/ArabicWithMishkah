<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\LegalDocument;
use Illuminate\Console\Command;

/**
 * Last-resort category fill for eastlaws docs the snapshot config didn't
 * surface. Uses Arabic title heuristics — broad keyword matching against
 * the document title — and falls back to 'civil-general' for ambiguous
 * Arabic-only laws (most untagged residue is general-civil-code material).
 *
 * Lawyers can re-tag manually from the Knowledge Base UI if a heuristic
 * match is wrong; this is a "make the table fully populated for the demo"
 * pass, not authoritative classification.
 */
class EastlawsBackfillCategoryCommand extends Command
{
    protected $signature = 'eastlaws:backfill-category
        {--dry-run : Print proposed labels without writing.}
        {--default=civil-general : Category to assign when no heuristic matches.}';

    protected $description = 'Heuristic category fill for eastlaws docs the snapshot config did not categorise.';

    /**
     * Title-substring → category mapping. Order matters — first match wins.
     * Tuned for Arabic eastlaws document titles which are usually a single
     * "قانون رقم X لسنة Y بشأن [...]" sentence revealing the subject.
     *
     * @var array<int, array{0:string, 1:string}>
     */
    private const KEYWORD_MAP = [
        ['شركات',                    'companies'],
        ['تجارية',                    'commercial'],
        ['تجارة',                    'commercial'],
        ['عمل',                       'labour'],
        ['عمالي',                    'labour'],
        ['تأمين الاجتماعي',           'social-insurance'],
        ['تأمينات الاجتماعية',        'social-insurance'],
        ['ضريب',                     'tax'],
        ['جمارك',                    'tax'],
        ['دمغة',                     'tax'],
        ['شهر العقاري',                'real-estate'],
        ['إيجار',                     'real-estate'],
        ['عقارية',                    'real-estate'],
        ['أحوال الشخصية',             'family'],
        ['أسرة',                     'family'],
        ['زواج',                     'family'],
        ['ميراث',                    'family'],
        ['مرافعات',                   'procedural'],
        ['إجراءات المدنية',           'procedural'],
        ['إثبات',                    'procedural'],
        ['عقوبات',                    'criminal'],
        ['جنائي',                    'criminal'],
        ['تحكيم',                    'arbitration'],
        ['أوراق المالية',             'capital-markets'],
        ['سوق المال',                 'capital-markets'],
        ['سوق رأس المال',             'capital-markets'],
        ['استثمار',                   'investment'],
        ['إفلاس',                    'insolvency'],
        ['إعادة الهيكلة',             'insolvency'],
        ['غسل الأموال',                'compliance'],
        ['مكافحة الرشوة',             'compliance'],
        ['مكافحة الفساد',             'compliance'],
        ['حماية المنافسة',            'compliance'],
        ['بنك',                      'banking'],
        ['مصرفي',                    'banking'],
        ['التنفيذ',                   'enforcement'],
        ['توثيق',                    'notarisation'],
        ['الموثقين',                  'notarisation'],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $default = (string) ($this->option('default') ?: 'civil-general');

        $docs = LegalDocument::query()
            ->where('source', 'eastlaws')
            ->whereNull('category')
            ->get();

        if ($docs->isEmpty()) {
            $this->info('No untagged eastlaws docs.');

            return self::SUCCESS;
        }

        $this->info("Inspecting {$docs->count()} untagged eastlaws docs.");
        $tally = [];

        foreach ($docs as $doc) {
            $cat = self::inferCategory((string) $doc->title) ?? $default;
            $tally[$cat] = ($tally[$cat] ?? 0) + 1;

            $this->line(sprintf(
                '  #%d  → <fg=cyan>%s</>  %s',
                $doc->id,
                $cat,
                mb_substr($doc->title, 0, 60)
            ));
            if (! $dryRun) {
                $doc->forceFill(['category' => $cat])->save();
            }
        }

        $this->newLine();
        $this->info('Backfill summary:');
        ksort($tally);
        foreach ($tally as $cat => $n) {
            $this->line('  '.$cat.': '.$n);
        }
        if ($dryRun) {
            $this->warn('--dry-run: nothing written.');
        }

        return self::SUCCESS;
    }

    public static function inferCategory(string $title): ?string
    {
        foreach (self::KEYWORD_MAP as [$needle, $cat]) {
            if (mb_stripos($title, $needle) !== false) {
                return $cat;
            }
        }

        return null;
    }
}
