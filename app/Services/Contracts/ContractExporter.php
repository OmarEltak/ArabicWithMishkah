<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Contract;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Generate downloadable artefacts (DOCX) from a saved Contract. The body is
 * treated as lightly-marked text with these conventions: blank lines separate
 * paragraphs, lines that look like ALL CAPS or "1." numbered headings get
 * heading style, lines starting with "*" or "-" become bullets.
 */
class ContractExporter
{
    /**
     * Generate a DOCX. If $bilingualLang is provided AND the contract has a
     * translation in that language, the output is a two-column side-by-side
     * Arabic / target-language layout (Arabic right, target left). Otherwise
     * a single-column Arabic-only document.
     *
     * Bilingual output includes a "WORKING TRANSLATION — NOT LEGALLY BINDING"
     * stamp on the target-language column and a language-prevailing footer.
     */
    public function toDocx(Contract $contract, ?string $bilingualLang = null): string
    {
        $word = new PhpWord;
        $word->setDefaultFontName('Calibri');
        $word->setDefaultFontSize(11);

        $word->addTitleStyle(1, ['bold' => true, 'size' => 16], ['alignment' => Jc::CENTER, 'spaceAfter' => 240]);
        $word->addTitleStyle(2, ['bold' => true, 'size' => 13], ['spaceBefore' => 240, 'spaceAfter' => 120]);
        $word->addParagraphStyle('body', ['alignment' => Jc::BOTH, 'spaceAfter' => 120]);
        $word->addParagraphStyle('body-rtl', ['alignment' => Jc::BOTH, 'spaceAfter' => 120, 'bidi' => true]);

        $section = $word->addSection([
            'marginTop' => 1134,
            'marginBottom' => 1134,
            'marginLeft' => 1134,
            'marginRight' => 1134,
        ]);

        $section->addTitle($contract->title, 1);

        $translations = is_array($contract->translations) ? $contract->translations : [];
        $hasTranslation = $bilingualLang !== null
            && isset($translations[$bilingualLang]['body'])
            && trim((string) $translations[$bilingualLang]['body']) !== '';

        if ($hasTranslation) {
            $this->writeBilingualBody($section, $contract, $bilingualLang, (array) $translations[$bilingualLang]);
        } else {
            $this->writeSingleBody($section, $contract);
        }

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($word, 'Word2007');
        $tmp = tempnam(sys_get_temp_dir(), 'docx_');
        $writer->save($tmp);

        return (string) file_get_contents($tmp);
    }

    public function suggestedFilename(Contract $contract, ?string $bilingualLang = null): string
    {
        $base = Str::slug($contract->title) ?: 'contract-'.$contract->id;
        $suffix = $bilingualLang ? '-bilingual-'.$bilingualLang : '';

        return $base.'-v'.$contract->version.$suffix.'.docx';
    }

    /**
     * Single-column Arabic body (legacy path). Extracted from toDocx for clarity.
     *
     * @param  \PhpOffice\PhpWord\Element\Section  $section
     */
    private function writeSingleBody($section, Contract $contract): void
    {
        $body = (string) $contract->body;
        $disclaimer = trim((string) config('lawyer.disclaimer', ''));
        if ($disclaimer !== '' && str_contains($body, $disclaimer)) {
            $body = trim(str_replace($disclaimer, '', $body));
            $body = rtrim($body, "-\n \t");
        }

        $paragraphs = preg_split('/\n{2,}/u', trim($body)) ?: [];
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }

            if (self::looksLikeHeading($paragraph)) {
                $section->addTitle(self::stripBullets($paragraph), 2);

                continue;
            }

            $lines = preg_split('/\n/', $paragraph) ?: [];
            $bulletLines = array_filter($lines, fn (string $l) => preg_match('/^\s*[\*\-•]\s+/u', $l) === 1);
            if (count($bulletLines) === count($lines) && count($lines) > 0) {
                foreach ($lines as $line) {
                    $section->addListItem(self::stripBullets($line), 0);
                }

                continue;
            }

            $section->addText(self::sanitiseRun($paragraph), null, 'body');
        }

        if ($disclaimer !== '') {
            $section->addTextBreak(1);
            $section->addText($disclaimer, ['italic' => true, 'size' => 9, 'color' => '666666']);
        }
    }

    /**
     * Two-column bilingual body. Right column = Arabic source (RTL,
     * legally binding). Left column = target-language working translation.
     * Paragraphs are zipped pairwise; if one side is shorter, blank cells
     * pad the layout.
     *
     * @param  \PhpOffice\PhpWord\Element\Section  $section
     * @param  array<string, mixed>  $translation  meta from Contract->translations[$lang]
     */
    private function writeBilingualBody($section, Contract $contract, string $lang, array $translation): void
    {
        $disclaimer = trim((string) config('lawyer.disclaimer', ''));
        $arabic = (string) $contract->body;
        $target = (string) ($translation['body'] ?? '');
        if ($disclaimer !== '' && str_contains($arabic, $disclaimer)) {
            $arabic = rtrim(str_replace($disclaimer, '', $arabic), "-\n \t");
        }

        $arabicParagraphs = preg_split('/\n{2,}/u', trim($arabic)) ?: [];
        $targetParagraphs = preg_split('/\n{2,}/u', trim($target)) ?: [];
        $rowCount = max(count($arabicParagraphs), count($targetParagraphs));

        // Header row: which column is which
        $section->addTextBreak(1);
        $headerTable = $section->addTable(['borderSize' => 0]);
        $headerTable->addRow();
        $hL = $headerTable->addCell(5500);
        $hL->addText(strtoupper($lang).' — Working translation, NOT legally binding', ['bold' => true, 'size' => 9, 'color' => '999999']);
        $hR = $headerTable->addCell(5500);
        $hR->addText('العربية — النص الملزم قانوناً', ['bold' => true, 'size' => 9, 'color' => '999999'], ['alignment' => Jc::END, 'bidi' => true]);

        // Body table
        $tableStyle = [
            'borderSize' => 4,
            'borderColor' => 'CCCCCC',
            'cellMargin' => 100,
            'cellSpacing' => 0,
        ];
        $word = $section->getPhpWord();
        $word->addTableStyle('bilingual-body', $tableStyle);
        $table = $section->addTable('bilingual-body');

        for ($i = 0; $i < $rowCount; $i++) {
            $arPara = isset($arabicParagraphs[$i]) ? trim($arabicParagraphs[$i]) : '';
            $enPara = isset($targetParagraphs[$i]) ? trim($targetParagraphs[$i]) : '';

            $table->addRow();
            // Left = target language (LTR)
            $cellL = $table->addCell(5500, ['valign' => 'top']);
            self::writeParagraphIntoCell($cellL, $enPara, false);
            // Right = Arabic (RTL)
            $cellR = $table->addCell(5500, ['valign' => 'top']);
            self::writeParagraphIntoCell($cellR, $arPara, true);
        }

        // Footer: language-prevailing clause + disclaimer
        $section->addTextBreak(1);
        $section->addText(
            'Language: This Agreement is executed in Arabic and '.strtoupper($lang).'. In case of discrepancy between the two versions, the ARABIC text shall prevail. The '.strtoupper($lang).' version is a working translation prepared for convenience only.',
            ['italic' => true, 'size' => 9, 'color' => '666666'],
        );
        $section->addTextBreak(1);
        $section->addText(
            'اللغة: حُرر هذا العقد باللغة العربية واللغة '.($lang === 'en' ? 'الإنجليزية' : 'الفرنسية').'. وفي حال أي تعارض بين النصين، يكون النص العربي هو الملزم قانوناً. النسخة المترجمة استرشادية فقط.',
            ['italic' => true, 'size' => 9, 'color' => '666666'],
            ['alignment' => Jc::END, 'bidi' => true],
        );

        if ($disclaimer !== '') {
            $section->addTextBreak(1);
            $section->addText($disclaimer, ['italic' => true, 'size' => 9, 'color' => '666666']);
        }

        // Translation footer — branded, no internal model/provider names.
        // The actual model is preserved in $translation['model'] for the
        // operator's audit log; the client-facing deliverable does not need
        // to expose which third-party LLM produced the text.
        $generatedAt = $translation['generated_at'] ?? '';
        $section->addTextBreak(1);
        $section->addText(
            'Translation prepared by My-lawyer AI on '.\Illuminate\Support\Carbon::parse($generatedAt ?: now())->toFormattedDateString().'.',
            ['italic' => true, 'size' => 8, 'color' => '999999'],
        );
    }

    /**
     * Write a single (possibly multi-line) paragraph into a table cell with
     * optional RTL/bidi formatting. Splits on newlines so each line is its
     * own paragraph in the cell.
     */
    private static function writeParagraphIntoCell($cell, string $paragraph, bool $rtl): void
    {
        if ($paragraph === '') {
            $cell->addText(' ');

            return;
        }
        $paraStyle = $rtl
            ? ['alignment' => Jc::END, 'bidi' => true, 'spaceAfter' => 60]
            : ['alignment' => Jc::START, 'spaceAfter' => 60];

        $isHeading = self::looksLikeHeading($paragraph);
        $textStyle = $isHeading ? ['bold' => true, 'size' => 12] : null;

        $lines = preg_split('/\n/', $paragraph) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $cell->addText(self::sanitiseRun($line), $textStyle, $paraStyle);
        }
    }

    private static function looksLikeHeading(string $paragraph): bool
    {
        if (str_contains($paragraph, "\n")) {
            return false;
        }
        if (mb_strlen($paragraph) > 120) {
            return false;
        }
        $trimmed = trim($paragraph, ".:;\n\t -–—");
        // ALL CAPS legal heading e.g. "MUTUAL NON-DISCLOSURE AGREEMENT".
        if (preg_match('/^[A-Z0-9 \-\(\)\.\/]+$/u', $trimmed) && mb_strlen($trimmed) > 4) {
            return true;
        }
        // Numbered section "1. PARTIES" or "Article 5 — …".
        if (preg_match('/^(\d+\.|Article\s+\d+|المادة\s+\d+)/u', $trimmed)) {
            return mb_strlen($trimmed) <= 90;
        }

        return false;
    }

    private static function stripBullets(string $line): string
    {
        return preg_replace('/^\s*[\*\-•]\s+/u', '', trim($line)) ?? trim($line);
    }

    private static function sanitiseRun(string $text): string
    {
        // PhpWord addText doesn't accept newlines as breaks; replace with explicit breaks.
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        return $text;
    }
}
