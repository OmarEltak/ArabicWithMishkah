<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Contract;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Str;

/**
 * Generate PDF artefacts from a saved Contract. Mirrors ContractExporter's
 * interface (`toPdf`, `suggestedFilename`) so the controller treats both
 * formats interchangeably.
 *
 * Dompdf renders HTML → PDF without an external binary; it bundles
 * DejaVu Sans which has full Unicode coverage (including Arabic). We
 * generate a single HTML document with `dir="rtl"` on the body so
 * Arabic-source contracts paginate correctly.
 */
class ContractPdfExporter
{
    /**
     * Generate a PDF. If $bilingualLang is provided AND the contract has a
     * translation in that language, the output is a two-column side-by-side
     * Arabic / target-language layout. Otherwise a single-column Arabic-only
     * document.
     */
    public function toPdf(Contract $contract, ?string $bilingualLang = null): string
    {
        $translations = is_array($contract->translations) ? $contract->translations : [];
        $hasTranslation = $bilingualLang !== null
            && isset($translations[$bilingualLang]['body'])
            && trim((string) $translations[$bilingualLang]['body']) !== '';

        $html = $hasTranslation
            ? $this->renderBilingual($contract, $bilingualLang, (array) $translations[$bilingualLang])
            : $this->renderSingle($contract);

        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return (string) $dompdf->output();
    }

    public function suggestedFilename(Contract $contract, ?string $bilingualLang = null): string
    {
        $base = Str::slug($contract->title) ?: 'contract-'.$contract->id;
        $suffix = $bilingualLang ? '-bilingual-'.$bilingualLang : '';

        return $base.'-v'.$contract->version.$suffix.'.pdf';
    }

    private function renderSingle(Contract $contract): string
    {
        $body = $this->stripDisclaimer((string) $contract->body);
        $rendered = $this->renderParagraphsHtml($body);

        $titleSafe = e($contract->title);
        $versionLine = e(__('Version :v', ['v' => $contract->version ?? 1]));

        return <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>{$this->css()}</style>
</head>
<body class="rtl">
    <header class="doc-header">
        <h1>{$titleSafe}</h1>
        <p class="version">{$versionLine}</p>
    </header>
    <main>
        {$rendered}
    </main>
</body>
</html>
HTML;
    }

    /**
     * @param  array<string, mixed>  $translation
     */
    private function renderBilingual(Contract $contract, string $lang, array $translation): string
    {
        $arBody = $this->renderParagraphsHtml($this->stripDisclaimer((string) $contract->body));
        $trBody = $this->renderParagraphsHtml($this->stripDisclaimer((string) ($translation['body'] ?? '')));

        $titleSafe = e($contract->title);
        $stamp = e(__('WORKING TRANSLATION — NOT LEGALLY BINDING'));
        $prevailing = e(__('In case of discrepancy, the Arabic version prevails.'));
        $langLabel = e(strtoupper($lang));

        return <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>{$this->css()}
        .bilingual { width: 100%; border-collapse: collapse; }
        .bilingual td { vertical-align: top; padding: 0 14px; width: 50%; }
        .bilingual td.col-rtl { direction: rtl; text-align: right; border-left: 1px solid #ddd; }
        .bilingual td.col-ltr { direction: ltr; text-align: left; }
        .stamp { font-size: 9pt; color: #b54708; margin-bottom: 6px; letter-spacing: 0.04em; }
        .prevailing { margin-top: 24px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 9pt; color: #666; }
    </style>
</head>
<body>
    <header class="doc-header">
        <h1 dir="rtl" style="text-align: center;">{$titleSafe}</h1>
    </header>
    <table class="bilingual">
        <tr>
            <td class="col-rtl">
                <div class="stamp">العربية — النص الملزم قانوناً</div>
                {$arBody}
            </td>
            <td class="col-ltr">
                <div class="stamp">{$langLabel} — {$stamp}</div>
                {$trBody}
            </td>
        </tr>
    </table>
    <p class="prevailing">{$prevailing}</p>
</body>
</html>
HTML;
    }

    /**
     * Convert raw body text into HTML paragraphs/headings/bullets. Mirrors
     * the heuristics in ContractExporter::writeSingleBody.
     */
    private function renderParagraphsHtml(string $body): string
    {
        $paragraphs = preg_split('/\n{2,}/u', trim($body)) ?: [];
        $out = '';
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }
            if (self::looksLikeHeading($paragraph)) {
                $out .= '<h2>'.e(self::stripBullets($paragraph)).'</h2>';

                continue;
            }
            $lines = preg_split('/\n/', $paragraph) ?: [];
            $bulletLines = array_filter($lines, fn (string $l) => preg_match('/^\s*[\*\-•]\s+/u', $l) === 1);
            if (count($bulletLines) === count($lines) && count($lines) > 0) {
                $out .= '<ul>';
                foreach ($lines as $line) {
                    $out .= '<li>'.e(self::stripBullets($line)).'</li>';
                }
                $out .= '</ul>';

                continue;
            }
            // Normal paragraph — preserve soft line breaks within with <br>.
            $escaped = e($paragraph);
            $withBreaks = str_replace("\n", '<br>', $escaped);
            $out .= '<p>'.$withBreaks.'</p>';
        }

        return $out;
    }

    private function stripDisclaimer(string $body): string
    {
        $disclaimer = trim((string) config('lawyer.disclaimer', ''));
        if ($disclaimer !== '' && str_contains($body, $disclaimer)) {
            $body = trim(str_replace($disclaimer, '', $body));
            $body = rtrim($body, "-\n \t");
        }

        return $body;
    }

    private function css(): string
    {
        return <<<'CSS'
            @page { margin: 22mm 18mm 22mm 18mm; }
            body { font-family: 'DejaVu Sans', sans-serif; font-size: 11pt; color: #1a1a1a; line-height: 1.6; }
            body.rtl { direction: rtl; }
            .doc-header { text-align: center; margin-bottom: 26px; }
            .doc-header h1 { font-size: 18pt; margin: 0 0 6px; font-weight: bold; }
            .doc-header .version { font-size: 9pt; color: #777; margin: 0; letter-spacing: 0.06em; text-transform: uppercase; }
            h2 { font-size: 13pt; font-weight: bold; margin: 18px 0 8px; padding-bottom: 4px; border-bottom: 1px solid #e5e7eb; }
            p { margin: 0 0 10px; text-align: justify; }
            ul { margin: 0 0 12px; padding-inline-start: 18px; }
            li { margin: 3px 0; }
CSS;
    }

    private static function looksLikeHeading(string $paragraph): bool
    {
        if (mb_strlen($paragraph) > 120 || str_contains($paragraph, "\n")) {
            return false;
        }
        if (preg_match('/^\s*\d+\.\s/u', $paragraph)) {
            return true;
        }
        if (preg_match('/^[A-Z\s\-—:،,]+$/u', $paragraph) && mb_strlen($paragraph) > 4) {
            return true;
        }

        return false;
    }

    private static function stripBullets(string $line): string
    {
        return preg_replace('/^\s*[\*\-•]\s+/u', '', $line) ?? $line;
    }
}
