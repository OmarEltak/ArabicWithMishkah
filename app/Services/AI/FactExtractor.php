<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Extracts structured facts from free-form intent text. Used to populate a
 * template's required_fields without making the user fill a separate form.
 *
 * The LLM is asked for JSON-only output matching a known key set. Tiny
 * prompt, tiny output, predictable structure — cheap for any provider.
 *
 * Falls back to an empty array on parse failure; the drafting flow then
 * marks unfilled slots as `[TO CONFIRM: key]` instead of crashing.
 */
class FactExtractor
{
    public function __construct(private readonly LlmInterface $llm) {}

    public static function fromConfig(): self
    {
        return new self(LlmFactory::default());
    }

    /**
     * @param  array<int, string>  $requiredKeys  field names from a template's required_fields
     * @param  string  $intent                    user-supplied free text describing the contract
     * @return array<string, string|int|float>
     */
    public function extract(array $requiredKeys, string $intent): array
    {
        if ($requiredKeys === [] || trim($intent) === '') {
            return [];
        }

        $keyList = implode(', ', array_map(fn ($k) => '"'.$k.'"', $requiredKeys));

        $system = <<<SYS
You extract facts from a user's plain-language description of a contract they need drafted.

Output ONLY a JSON object — no markdown, no commentary. Keys are restricted to: {$keyList}.
- Match values from the description verbatim where possible.
- Numeric fields (prices, areas, durations) should be numbers, not strings.
- If a key isn't present in the description, OMIT it (don't guess).
- If the user wrote in Arabic, keep names/places in Arabic.

Example output:  {"seller": "أحمد محمد", "price_egp": 2000000, "land_area_m2": 500}
SYS;

        $response = $this->llm->chat(
            messages: [['role' => 'user', 'content' => "Description:\n".$intent]],
            options: [
                'system' => $system,
                'max_tokens' => 400,
                'temperature' => 0.1,
            ],
        );

        $facts = self::parseJson($response['content'] ?? '');

        // Filter to only the requested keys; drop anything the model invented.
        return array_intersect_key($facts, array_flip($requiredKeys));
    }

    /**
     * @return array<string, mixed>
     */
    public static function parseJson(string $text): array
    {
        $trimmed = trim($text);
        $trimmed = preg_replace('/^```(?:json)?\s*|\s*```$/u', '', $trimmed) ?? $trimmed;
        $start = strpos($trimmed, '{');
        $end = strrpos($trimmed, '}');
        if ($start === false || $end === false || $end <= $start) {
            return [];
        }
        $candidate = substr($trimmed, $start, $end - $start + 1);
        $decoded = json_decode($candidate, true);

        return is_array($decoded) ? $decoded : [];
    }
}
