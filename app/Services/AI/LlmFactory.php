<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Picks the active LLM backend.
 *
 *   LAWYER_LLM_FALLBACK=true (default)
 *     → returns LlmFailoverChain wrapping primary + secondaries. The
 *       primary is config('lawyer.llm_provider'); secondaries are the
 *       remaining configured providers in a fixed order. When all keys
 *       in the primary's pool 429, the chain transparently moves to the
 *       next provider. See LlmFailoverChain for the circuit-breaker
 *       semantics.
 *
 *   LAWYER_LLM_FALLBACK=false
 *     → single-provider mode (legacy behaviour). If that provider's key
 *       is missing, its mock response surfaces — we don't silently fall
 *       through.
 *
 * Set the env to false for evaluation runs where you want to compare
 * specific provider behaviour without the chain re-routing requests.
 */
class LlmFactory
{
    /** Order tried when fallback is enabled, after the user-selected primary. */
    private const FALLBACK_ORDER = ['anthropic', 'gemini', 'groq'];

    public static function default(): LlmInterface
    {
        $primary = (string) config('lawyer.llm_provider', 'anthropic');
        $fallback = (bool) config('lawyer.llm_fallback', true);

        if (! $fallback) {
            return self::buildProvider($primary);
        }

        // Build chain: primary first, then the rest in FALLBACK_ORDER (no
        // duplicates). All three are constructed but only configured ones
        // participate in the failover; the chain skips unconfigured providers.
        $names = [$primary];
        foreach (self::FALLBACK_ORDER as $name) {
            if ($name !== $primary) {
                $names[] = $name;
            }
        }

        $providers = array_map([self::class, 'buildProvider'], $names);

        return new LlmFailoverChain($providers);
    }

    private static function buildProvider(string $name): LlmInterface
    {
        return match ($name) {
            'groq' => GroqService::fromConfig(),
            'gemini' => GeminiService::fromConfig(),
            default => AnthropicService::fromConfig(),
        };
    }
}
