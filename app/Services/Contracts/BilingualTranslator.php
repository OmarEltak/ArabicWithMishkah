<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Services\AI\LlmInterface;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * Produces working English (or French) translations of Arabic legal contracts
 * drafted under MENA Arab civil law.
 *
 * The Arabic version remains the legally binding source; this service produces
 * a working aid for non-Arabic-reading parties. Quality strategy:
 *
 *  1. Glossary lock — a curated terminology file pinned into the system prompt
 *     forces the model to use accepted English equivalents for civil-law terms
 *     of art (e.g. الفسخ → "rescission", not "termination"; حسن النية → civil-law
 *     good faith, narrower than common-law).
 *
 *  2. Citation preservation — Arabic statute references (المادة 159 من القانون
 *     المدني) MUST be kept verbatim with parenthetical English. The binding
 *     citation is the Arabic; English is an aid.
 *
 *  3. Low temperature — translation should be deterministic. We override to 0.2
 *     regardless of the configured drafting temperature.
 *
 *  4. Bounded output budget — translation is shorter or equal to source; we
 *     allow 2x source length to be safe.
 *
 * Returned shape:
 *   ['body' => '...', 'model' => '...', 'generated_at' => '...',
 *    'glossary_jurisdiction' => 'EG']
 */
class BilingualTranslator
{
    private const SUPPORTED_LANGS = ['en', 'fr'];

    public function __construct(
        private readonly LlmInterface $llm,
        private readonly string $glossaryDir,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            llm: app(LlmInterface::class),
            glossaryDir: resource_path('legal'),
        );
    }

    /**
     * Translate the contract body to the target language. Returns the metadata
     * shape that callers should persist as Contract->translations[$lang].
     *
     * @return array{body:string, model:string, generated_at:string, glossary_jurisdiction:string, warnings:array<int,string>}
     */
    public function translate(
        string $arabicBody,
        string $targetLanguage,
        string $jurisdiction = 'EG',
    ): array {
        if (! in_array($targetLanguage, self::SUPPORTED_LANGS, true)) {
            throw new RuntimeException("Unsupported target language: {$targetLanguage}");
        }

        $arabicBody = trim($arabicBody);
        if ($arabicBody === '') {
            throw new RuntimeException('Empty contract body — nothing to translate.');
        }

        $jurisdiction = strtoupper($jurisdiction);

        // Cache lookup — same body + lang + jurisdiction means a previously
        // produced translation is still valid. Save a $0.02–$0.05 LLM call
        // and ~10s of wall time per repeat translation. The cache key is
        // hashed so it's safe to use as a key prefix even for huge bodies.
        // 30-day TTL because legal text rarely changes that fast; if a
        // user wants to force a re-translation, the cache key shifts the
        // moment they edit a single character.
        $cacheKey = 'translation:'.hash('sha256', $arabicBody.'|'.$targetLanguage.'|'.$jurisdiction);
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && isset($cached['body'])) {
            return $cached + ['cache_hit' => true];
        }

        [$glossary, $glossaryJurisdiction] = $this->loadGlossary($jurisdiction);
        $system = $this->buildSystemPrompt($glossary, $targetLanguage, $glossaryJurisdiction);

        // Bounded output budget. Translation output ~ source length, but
        // Gemini's "thinking" tokens count against maxOutputTokens — so we
        // both disable thinking (translation is deterministic, no reasoning
        // needed) AND give a generous ceiling to absorb verbose glossary-
        // expanded outputs.
        $maxTokens = max(8000, min(32000, mb_strlen($arabicBody) * 4));

        $response = $this->llm->chat([
            ['role' => 'user', 'content' => $arabicBody],
        ], [
            'system' => $system,
            'max_tokens' => $maxTokens,
            'temperature' => 0.2,
            // Disables Gemini 2.5 Flash extended thinking. Reclaims the full
            // output budget for the actual translation. Ignored by other
            // providers (Anthropic / Groq) that don't have a thinking knob.
            'thinking_budget' => 0,
        ]);

        $body = trim((string) ($response['content'] ?? ''));
        if ($body === '') {
            throw new RuntimeException('Translator returned empty content. Try again.');
        }

        // Surface translator self-flagged uncertainties so the UI can hint to
        // the lawyer where review matters most.
        $warnings = [];
        if (preg_match_all('/\[\?:\s*([^\]]+)\]/u', $body, $m) > 0) {
            $warnings = array_values(array_unique($m[1]));
        }

        $result = [
            'body' => $body,
            'model' => (string) ($response['raw']['model'] ?? config('services.gemini.model', 'unknown')),
            'generated_at' => now()->toIso8601String(),
            'glossary_jurisdiction' => $glossaryJurisdiction,
            'warnings' => $warnings,
        ];

        Cache::put($cacheKey, $result, now()->addDays(30));

        return $result + ['cache_hit' => false];
    }

    /**
     * Load the glossary for the requested jurisdiction. Falls back to EG if
     * the jurisdiction-specific file is missing.
     *
     * @return array{0:string, 1:string} [glossary content, jurisdiction actually used]
     */
    private function loadGlossary(string $jurisdiction): array
    {
        $iso = strtolower($jurisdiction);
        $cacheKey = 'translation.glossary.'.$iso;

        return Cache::driver('array')->rememberForever($cacheKey, function () use ($iso, $jurisdiction): array {
            $primary = $this->glossaryDir.'/translation-glossary-'.$iso.'.md';
            if (is_file($primary)) {
                return [(string) file_get_contents($primary), $jurisdiction];
            }
            $fallback = $this->glossaryDir.'/translation-glossary-eg.md';
            if (is_file($fallback)) {
                return [(string) file_get_contents($fallback), 'EG'];
            }

            return ['', $jurisdiction];
        });
    }

    private function buildSystemPrompt(string $glossary, string $targetLanguage, string $jurisdiction): string
    {
        $langName = match ($targetLanguage) {
            'en' => 'English',
            'fr' => 'French',
            default => 'English',
        };

        $glossarySection = $glossary !== ''
            ? "GLOSSARY ({$jurisdiction} terminology — USE THESE EXACT EQUIVALENTS):\n{$glossary}\n"
            : "(No glossary available for this jurisdiction. Translate carefully.)\n";

        return <<<PROMPT
You are a senior Cairo-trained legal translator producing {$langName} working translations of Arabic-language contracts drafted under {$jurisdiction} civil law.

The Arabic version is the LEGALLY BINDING source. Your output is a working aid for non-Arabic-reading parties. Therefore:

1. PRESERVE all Arabic statute citations VERBATIM, then add the {$langName} translation in parentheses. Example:
   Source:   "وفقاً للمادة 159 من القانون المدني المصري"
   Output:   "pursuant to المادة 159 من القانون المدني المصري (Article 159 of the Egyptian Civil Code)"
   The same applies to law numbers (قانون رقم 12 لسنة 2003), articles, and decree references.

2. USE THE GLOSSARY EQUIVALENT EXACTLY when an Arabic term in the glossary appears. The glossary captures terms whose civil-law meaning differs from common-law equivalents — translating them loosely changes the legal effect.

3. PROPER NOUNS:
   - Party names: TRANSLITERATE to the closest English form (e.g. محمد → Mohamed, طارق → Tarek, شركة الفيصل القابضة → Al-Faisal Holding Company). The Arabic spelling on the right pane is the binding form; the English is for non-Arabic readers to identify whom the document refers to.
   - Names of cities and government authorities: use the established English name from the glossary (e.g. الهيئة العامة للاستثمار → General Authority for Investment and Free Zones (GAFI)). If not in glossary, transliterate.
   - Currency amounts in figures: convert ج.م. → EGP, ر.س. → SAR, د.إ. → AED, د.ك. → KWD, ر.ق. → QAR, د.ب. → BHD, ر.ع. → OMR, د.أ. → JOD, ل.ل. → LBP. Keep the figure as-is; do NOT spell out numbers.

4. MARK UNCERTAIN TRANSLATIONS with `[?: <Arabic original>]`. Better to flag than guess. The lawyer reviewing the translation will resolve flagged items.

5. STRUCTURE: Mirror the Arabic structure exactly — same numbered articles, same order of clauses, same paragraph breaks. Do NOT add lawyer-explainer commentary. Do NOT summarize. Translate clause-by-clause.

6. TONE: formal legal {$langName} / corporate-counsel register. Avoid common-law boilerplate spam ("shall not"-shall not"). Match Egyptian civil-law precision (which is more affirmative/positive than common-law tradition).

7. OUTPUT FORMAT: Output ONLY the translated contract body. No preamble like "Here is the translation:". No closing "Disclaimer:". Start directly with the translated heading. Do not use markdown formatting (no `**bold**`, no `# headings`, no `*` bullets) — match the plain-text format of the source.

{$glossarySection}
PROMPT;
    }
}
