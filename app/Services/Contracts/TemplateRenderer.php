<?php

declare(strict_types=1);

namespace App\Services\Contracts;

/**
 * Server-side `{{placeholder}}` substitution. Pulled out of the LLM's
 * responsibility because smaller open models (Llama 3.1 8b) reliably miss
 * the substitution step. Doing it deterministically guarantees correctness
 * regardless of model capability.
 *
 * Missing keys are replaced with `[TO CONFIRM: key]` rather than left raw,
 * so any unfilled slot is visible to the lawyer at review time.
 *
 * Includes a `normaliseFacts()` pass that resolves relative date phrases
 * ("today", "اليوم", "now") into actual ISO/locale-formatted dates BEFORE
 * substitution — a common LLM-extraction blind spot for routine contracts.
 */
class TemplateRenderer
{
    /** Heuristic match for keys that semantically hold a date. */
    private const DATE_KEY_PATTERNS = ['date', 'when', 'on', 'تاريخ', 'يوم'];

    /** Words across EN/AR that mean "today" — resolved to today's actual date. */
    private const TODAY_TOKENS = ['today', 'now', 'اليوم', 'الآن', 'هذا اليوم'];

    /**
     * Normalise extracted facts before substitution. Currently:
     *   - Resolves date-typed values containing relative phrases like "today"
     *     to an actual date in the document's language.
     *   - Fills MISSING date-typed keys with today's date.
     *
     * Date-typed keys are detected from the template's required_fields map
     * (the `'date'` type annotation). For templates without a typed schema,
     * we also pattern-match key names ending in "_date" / "تاريخ".
     *
     * @param  array<string, mixed>  $facts
     * @param  array<string, string>  $fieldTypes  e.g. ['effective_date' => 'date']
     */
    public static function normaliseFacts(array $facts, array $fieldTypes = [], string $locale = 'en'): array
    {
        $dateKeys = array_keys(array_filter($fieldTypes, fn ($t) => $t === 'date'));

        // Backstop: also pattern-match key names so untyped templates still work.
        foreach (array_keys($facts) as $key) {
            if (self::looksLikeDateKey($key) && ! in_array($key, $dateKeys, true)) {
                $dateKeys[] = $key;
            }
        }

        foreach ($dateKeys as $key) {
            $raw = $facts[$key] ?? null;
            if (self::isTodayToken($raw) || $raw === null || $raw === '') {
                $facts[$key] = self::today($locale);
            } elseif (is_string($raw)) {
                $parsed = self::tryParseDate($raw);
                if ($parsed !== null) {
                    $facts[$key] = self::formatDate($parsed, $locale);
                }
            }
        }

        return $facts;
    }

    private static function looksLikeDateKey(string $key): bool
    {
        $lower = mb_strtolower($key);
        foreach (self::DATE_KEY_PATTERNS as $needle) {
            if (str_ends_with($lower, '_'.$needle) || str_ends_with($lower, $needle)) {
                return true;
            }
        }

        return false;
    }

    private static function isTodayToken(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }
        $needle = mb_strtolower(trim($value));
        foreach (self::TODAY_TOKENS as $token) {
            if ($needle === mb_strtolower($token)) {
                return true;
            }
        }

        return false;
    }

    private static function tryParseDate(string $value): ?\DateTimeInterface
    {
        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function today(string $locale): string
    {
        return self::formatDate(\Carbon\Carbon::today(), $locale);
    }

    private static function formatDate(\DateTimeInterface $date, string $locale): string
    {
        $carbon = \Carbon\Carbon::instance($date);

        // Long, human-friendly per locale. Arabic gets "٦ مايو ٢٠٢٦" via
        // Carbon's built-in `ar` locale; English gets "6 May 2026".
        if (str_starts_with($locale, 'ar')) {
            return $carbon->locale('ar')->isoFormat('D MMMM YYYY');
        }

        return $carbon->isoFormat('D MMMM YYYY');
    }

    /**
     * Render `{{key}}` markers in $body using $facts.
     *
     * @param  array<string, mixed>  $facts
     */
    public static function render(string $body, array $facts): string
    {
        return preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/u',
            function ($matches) use ($facts) {
                $key = $matches[1];
                $value = $facts[$key] ?? null;
                if ($value === null || $value === '' || (is_array($value) && $value === [])) {
                    return '[TO CONFIRM: '.$key.']';
                }
                if (is_array($value)) {
                    $value = implode(', ', array_map('strval', $value));
                }
                if (is_bool($value)) {
                    $value = $value ? 'yes' : 'no';
                }

                return (string) $value;
            },
            $body
        ) ?? $body;
    }

    /**
     * Inverse: list every `{{key}}` referenced in the body, deduplicated.
     *
     * @return array<int, string>
     */
    public static function placeholders(string $body): array
    {
        if (! preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/u', $body, $matches)) {
            return [];
        }

        return array_values(array_unique($matches[1]));
    }
}
