<?php

declare(strict_types=1);

namespace App\Services\Ingestion;

/**
 * Turns the raw text we extracted from upstream HTML into a structured form
 * suitable for reading: UI cruft stripped, article boundaries detected,
 * sections grouped by header.
 *
 * The upstream extraction (EastlawsClient::htmlToCleanText) is intentionally
 * dumb — it strips tags and collapses whitespace, nothing more — so the same
 * legal_documents row supports re-formatting later without re-ingesting.
 * This service is where the presentation intelligence lives.
 */
final class LegalContentFormatter
{
    /**
     * Lines that exactly match these patterns are eastlaws UI labels that
     * leaked into the text-extraction pass. They carry no legal content.
     *
     * @var array<int, string>
     */
    private const NOISE_LINES = [
        'AI',
        'بحث:',
        'بحث بكلمة دالة:',
        'تصفية النتائج',
        'تلوين الكلمات فقط',
        'انتقل إلى المادة رقم:',
        'يتم حالياً تجهيز النص للطباعة ... برجاء الإنتظار',
        'استخدم الضغط المزدوج "Double - click" للوصول إلى المادة في التبويب المستهدف',
        '|',
        '×',
    ];

    /**
     * Lines matching these regexes are also noise. Used for variable patterns
     * (e.g. stray digit-only lines from pagination widgets, single-char rows).
     *
     * @var array<int, string>
     */
    private const NOISE_PATTERNS = [
        '/^\d{1,3}$/u',                    // stray digits (pagination)
        '/^[\s\.\-]+$/u',                  // punctuation-only lines
    ];

    /**
     * Headings that should anchor a new section. Matched against trimmed
     * lines. The matched line becomes the section heading; everything until
     * the next heading is its body.
     *
     * The order matters — the article-pattern test runs first to capture
     * `مادة 1 اتفاقية` style hits before the bare `مادة` keyword test.
     *
     * @var array<int, string>
     */
    private const HEADING_PATTERNS = [
        '/^مادة\s+\d+(?:\s+\S+)?\s*$/u',                       // مادة 1 / مادة 1 اتفاقية
        '/^الفصل\s+\S+/u',                                       // الفصل الأول
        '/^الباب\s+\S+/u',                                       // الباب الأول
        '/^القسم\s+\S+/u',                                       // القسم الأول
        '/^الجزء\s+\S+/u',                                       // الجزء الأول
        '/^(ديباجة|مقدمة|تمهيد|الخاتمة|الفهرس)\b/u',           // standalone preamble / closing keywords
    ];

    /**
     * Headings that introduce article-level content (rendered with a stronger
     * visual weight than plain section headings).
     *
     * @var array<int, string>
     */
    private const ARTICLE_PATTERNS = [
        '/^مادة\s+\d+/u',
    ];

    /**
     * Parse a raw content string into a list of sections.
     *
     * Each section is:
     *   [ 'heading' => ?string, 'body' => string, 'is_article' => bool ]
     *
     * The body is the trimmed text content between this heading and the
     * next one. The first section may have heading=null (leading prose
     * before any recognised heading).
     *
     * @return array<int, array{heading: ?string, body: string, is_article: bool}>
     */
    public function format(string $raw): array
    {
        $clean = $this->stripNoise($raw);
        if ($clean === '') {
            return [];
        }

        $lines = preg_split('/\n+/u', $clean) ?: [];
        $sections = [];
        /** @var array{heading: ?string, body: string, is_article: bool}|null $current */
        $current = null;

        foreach ($lines as $rawLine) {
            $line = trim($rawLine);
            if ($line === '') {
                continue;
            }

            if ($this->isHeading($line)) {
                if ($current !== null && ($current['heading'] !== null || trim($current['body']) !== '')) {
                    $current['body'] = trim($current['body']);
                    $sections[] = $current;
                }
                $current = [
                    'heading' => $line,
                    'body' => '',
                    'is_article' => $this->isArticleHeading($line),
                ];

                continue;
            }

            if ($current === null) {
                $current = ['heading' => null, 'body' => '', 'is_article' => false];
            }
            $current['body'] .= ($current['body'] === '' ? '' : "\n").$line;
        }

        if ($current !== null && ($current['heading'] !== null || trim($current['body']) !== '')) {
            $current['body'] = trim($current['body']);
            $sections[] = $current;
        }

        return $sections;
    }

    /**
     * Remove eastlaws UI noise lines from the raw text. Returns the
     * stripped text suitable for downstream parsing.
     */
    public function stripNoise(string $raw): string
    {
        $lines = preg_split('/\r?\n/u', $raw) ?: [];
        $kept = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                $kept[] = '';

                continue;
            }
            if (in_array($trimmed, self::NOISE_LINES, true)) {
                continue;
            }
            $noise = false;
            foreach (self::NOISE_PATTERNS as $pat) {
                if (preg_match($pat, $trimmed) === 1) {
                    $noise = true;
                    break;
                }
            }
            if ($noise) {
                continue;
            }
            $kept[] = $trimmed;
        }

        // Collapse runs of blank lines.
        $out = preg_replace('/\n{3,}/u', "\n\n", implode("\n", $kept)) ?? implode("\n", $kept);

        return trim($out);
    }

    private function isHeading(string $line): bool
    {
        foreach (self::HEADING_PATTERNS as $pat) {
            if (preg_match($pat, $line) === 1) {
                return true;
            }
        }

        return false;
    }

    private function isArticleHeading(string $line): bool
    {
        foreach (self::ARTICLE_PATTERNS as $pat) {
            if (preg_match($pat, $line) === 1) {
                return true;
            }
        }

        return false;
    }
}
