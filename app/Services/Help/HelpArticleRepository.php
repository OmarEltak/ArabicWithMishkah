<?php

declare(strict_types=1);

namespace App\Services\Help;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use League\CommonMark\GithubFlavoredMarkdownConverter;

/**
 * Loads /help/*.md articles into memory once per request, parses YAML-style
 * front-matter, renders body to HTML via GitHub-flavored markdown.
 *
 * Article files live in `resources/help/{locale}/*.md` (per-language). The
 * locale is the user's app locale ('ar' or 'en'); falls back to 'en' when
 * the requested article is missing in the target language.
 *
 * Format example:
 *
 *   ---
 *   title: Drafting your first contract
 *   section: Drafting
 *   order: 10
 *   ---
 *
 *   Markdown body…
 *
 * Cached in-memory for a minute so the directory isn't re-scanned on
 * every list-page render.
 */
class HelpArticleRepository
{
    private GithubFlavoredMarkdownConverter $converter;

    public function __construct()
    {
        $this->converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'escape', // never trust article HTML
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * All articles in the user's locale (fallback English).
     *
     * @return array<int, array{slug:string, title:string, section:string, order:int, locale:string}>
     */
    public function index(string $locale): array
    {
        return Cache::remember('help.index.'.$locale, 60, function () use ($locale): array {
            $files = $this->collectFiles($locale);
            $rows = [];
            foreach ($files as $slug => $path) {
                $meta = $this->parseFrontMatter((string) file_get_contents($path));
                $rows[] = [
                    'slug' => $slug,
                    'title' => (string) ($meta['title'] ?? Str::headline($slug)),
                    'section' => (string) ($meta['section'] ?? 'General'),
                    'order' => (int) ($meta['order'] ?? 100),
                    'locale' => $this->localeForFile($path, $locale),
                ];
            }
            usort($rows, function ($a, $b) {
                return $a['section'] <=> $b['section']
                    ?: $a['order'] <=> $b['order']
                    ?: $a['title'] <=> $b['title'];
            });

            return $rows;
        });
    }

    /**
     * A single article by slug, with body rendered to HTML.
     *
     * @return array{slug:string, title:string, section:string, body_html:string, locale:string}|null
     */
    public function find(string $slug, string $locale): ?array
    {
        $slug = Str::slug($slug); // defence: limit to alnum + hyphens
        if ($slug === '') {
            return null;
        }

        $path = $this->resolveFile($slug, $locale);
        if ($path === null) {
            return null;
        }

        $raw = (string) file_get_contents($path);
        $meta = $this->parseFrontMatter($raw);
        $body = $this->stripFrontMatter($raw);

        return [
            'slug' => $slug,
            'title' => (string) ($meta['title'] ?? Str::headline($slug)),
            'section' => (string) ($meta['section'] ?? 'General'),
            'body_html' => (string) $this->converter->convert($body),
            'locale' => $this->localeForFile($path, $locale),
        ];
    }

    /**
     * Free-text search across title + body.
     *
     * @return array<int, array{slug:string, title:string, section:string, snippet:string}>
     */
    public function search(string $locale, string $needle): array
    {
        $needle = trim($needle);
        if ($needle === '' || mb_strlen($needle) < 2) {
            return [];
        }
        $files = $this->collectFiles($locale);
        $needleLower = mb_strtolower($needle);

        $hits = [];
        foreach ($files as $slug => $path) {
            $raw = (string) file_get_contents($path);
            $meta = $this->parseFrontMatter($raw);
            $body = $this->stripFrontMatter($raw);
            $titleLower = mb_strtolower((string) ($meta['title'] ?? $slug));
            $bodyLower = mb_strtolower($body);

            if (str_contains($titleLower, $needleLower) || str_contains($bodyLower, $needleLower)) {
                $hits[] = [
                    'slug' => $slug,
                    'title' => (string) ($meta['title'] ?? Str::headline($slug)),
                    'section' => (string) ($meta['section'] ?? 'General'),
                    'snippet' => $this->makeSnippet($body, $needle),
                ];
            }
        }

        return $hits;
    }

    /**
     * @return array<string, string> slug => absolute path
     */
    private function collectFiles(string $locale): array
    {
        $localised = resource_path('help/'.$locale);
        $fallback = resource_path('help/en');

        $result = [];
        // Fallback first, localised overrides.
        foreach ([$fallback, $localised] as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            foreach (glob($dir.'/*.md') ?: [] as $path) {
                $slug = Str::slug(pathinfo($path, PATHINFO_FILENAME));
                $result[$slug] = $path;
            }
        }

        return $result;
    }

    private function resolveFile(string $slug, string $locale): ?string
    {
        $localised = resource_path('help/'.$locale.'/'.$slug.'.md');
        if (is_file($localised)) {
            return $localised;
        }
        $fallback = resource_path('help/en/'.$slug.'.md');
        if (is_file($fallback)) {
            return $fallback;
        }

        return null;
    }

    private function localeForFile(string $path, string $requested): string
    {
        if (str_contains($path, DIRECTORY_SEPARATOR.$requested.DIRECTORY_SEPARATOR)) {
            return $requested;
        }

        return 'en';
    }

    /**
     * @return array<string, mixed>
     */
    private function parseFrontMatter(string $raw): array
    {
        if (! preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $raw, $m)) {
            return [];
        }
        $meta = [];
        foreach (explode("\n", trim($m[1])) as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }
            [$k, $v] = explode(':', $line, 2);
            $meta[trim($k)] = trim($v);
        }

        return $meta;
    }

    private function stripFrontMatter(string $raw): string
    {
        return (string) preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $raw);
    }

    private function makeSnippet(string $body, string $needle): string
    {
        $bodyClean = trim((string) preg_replace('/\s+/u', ' ', $body));
        $idx = mb_stripos($bodyClean, $needle);
        if ($idx === false) {
            return mb_substr($bodyClean, 0, 200).(mb_strlen($bodyClean) > 200 ? '…' : '');
        }
        $start = max(0, $idx - 80);

        return ($start > 0 ? '…' : '').mb_substr($bodyClean, $start, 200).'…';
    }
}
