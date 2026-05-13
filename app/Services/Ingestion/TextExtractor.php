<?php

declare(strict_types=1);

namespace App\Services\Ingestion;

use RuntimeException;
use ZipArchive;

/**
 * Extract plain text from common document formats without external dependencies.
 *
 * Supported: .txt, .md, .csv (treated as text), .docx (zipped XML), .pdf (best-effort).
 * For high-quality PDF extraction in production, integrate smalot/pdfparser.
 */
class TextExtractor
{
    public function extractFromUpload(string $filePath, string $originalName): string
    {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        return match ($ext) {
            'txt', 'md', 'markdown', 'csv', 'json', 'html', 'htm' => $this->extractText($filePath, $ext),
            'docx' => $this->extractDocx($filePath),
            'pdf' => $this->extractPdf($filePath),
            default => throw new RuntimeException("Unsupported file type: .$ext"),
        };
    }

    private function extractText(string $path, string $ext): string
    {
        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException('Failed to read uploaded file.');
        }
        if ($ext === 'html' || $ext === 'htm') {
            return self::stripHtml($content);
        }

        return $content;
    }

    private function extractDocx(string $path): string
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Failed to open .docx file.');
        }
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if ($xml === false) {
            throw new RuntimeException('Failed to read document.xml from .docx');
        }

        // Replace paragraph and tab boundaries with whitespace before stripping tags.
        $xml = preg_replace('/<w:p[^>]*\/>/u', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<\/w:p>/u', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<w:tab[^>]*\/>/u', "\t", $xml) ?? $xml;
        $xml = preg_replace('/<w:br[^>]*\/>/u', "\n", $xml) ?? $xml;

        $text = strip_tags($xml);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return self::collapseWhitespace($text);
    }

    private function extractPdf(string $path): string
    {
        // Primary: smalot/pdfparser. Falls back to a regex-based extractor
        // for PDFs that pdfparser can't handle (rare).
        try {
            $parser = new \Smalot\PdfParser\Parser;
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();
            $clean = self::collapseWhitespace($text);
            if (trim($clean) !== '') {
                return $clean;
            }
        } catch (\Throwable $e) {
            // Fall through to regex parser below.
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException('Failed to read PDF.');
        }
        $output = '';
        if (preg_match_all('/stream\s*([\s\S]*?)\s*endstream/u', $raw, $matches) === false) {
            return '';
        }
        foreach ($matches[1] ?? [] as $stream) {
            $decoded = @gzuncompress(ltrim($stream));
            if ($decoded === false) {
                $decoded = $stream;
            }
            if (preg_match_all('/\(((?:\\\\.|[^()\\\\])*)\)\s*T[jJ]/u', $decoded, $tjMatches)) {
                foreach ($tjMatches[1] as $piece) {
                    $output .= self::decodePdfString($piece).' ';
                }
                $output .= "\n";
            }
        }

        $output = self::collapseWhitespace($output);
        if (trim($output) === '') {
            throw new RuntimeException('Could not extract text from this PDF (it may be scanned/encrypted). Please paste the text manually.');
        }

        return $output;
    }

    private static function decodePdfString(string $s): string
    {
        $s = str_replace(['\\(', '\\)', '\\\\', '\\n', '\\r', '\\t'], ['(', ')', '\\', "\n", "\r", "\t"], $s);

        return $s;
    }

    public static function stripHtml(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>[\s\S]*?<\/script>/iu', ' ', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>[\s\S]*?<\/style>/iu', ' ', $html) ?? $html;
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return self::collapseWhitespace($text);
    }

    public static function collapseWhitespace(string $text): string
    {
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
