<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Chains multiple LlmInterface implementations so a request that fails on
 * the primary provider transparently falls through to the next one.
 *
 * Layering against the existing key-pool rotation:
 *
 *   request → [primary provider]
 *               └── tries each key in its pool (Gemini: 3 keys @ 1500 RPD)
 *                   ↳ all keys exhausted (429 / 401 / 403) → throws
 *           → [next provider]   ← we catch the throw and try here
 *               └── same key-pool retry behaviour
 *           → [last provider]
 *               └── …
 *           → if all fail: bubble RuntimeException with combined errors
 *
 * Failover triggers on ANY throwable except BudgetExceededException
 * (the user's own daily-cost cap should NOT silently fall over to a
 * different provider — that defeats the cost ceiling). 400 schema errors
 * also burn one cross-provider attempt; that's fine since they're rare
 * and the next provider rejects fast.
 *
 * After a provider fails we cache a circuit-breaker flag for 5 minutes so
 * subsequent requests in the same window skip it on the first pass instead
 * of re-tripping. Survives a request boundary because we use the default
 * cache driver (not 'array' which is request-scoped). The cache key is
 * keyed by class name so different chain compositions don't collide.
 */
class LlmFailoverChain implements LlmInterface
{
    private const CIRCUIT_TTL_SECONDS = 300;

    public function __construct(
        /** @var array<int, LlmInterface> */
        private readonly array $providers,
    ) {}

    public function isConfigured(): bool
    {
        foreach ($this->providers as $p) {
            if ($p->isConfigured()) {
                return true;
            }
        }

        return false;
    }

    public function chat(array $messages, array $options = []): array
    {
        return $this->dispatch('chat', fn (LlmInterface $p) => $p->chat($messages, $options));
    }

    public function chatStream(array $messages, array $options, callable $onDelta): array
    {
        return $this->dispatch('chatStream', fn (LlmInterface $p) => $p->chatStream($messages, $options, $onDelta));
    }

    /**
     * Walk the provider chain. Configured-and-non-tripped first; tripped
     * providers are tried last as a "maybe it recovered" pass.
     *
     * @param  callable(LlmInterface):array<string,mixed>  $call
     * @return array<string,mixed>
     */
    private function dispatch(string $op, callable $call): array
    {
        $configured = array_values(array_filter(
            $this->providers,
            fn (LlmInterface $p) => $p->isConfigured()
        ));

        if ($configured === []) {
            // No configured providers — let the first one's mock response
            // surface so dev environments without keys still work.
            if ($this->providers === []) {
                throw new RuntimeException('No LLM providers wired into failover chain.');
            }

            return $call($this->providers[0]);
        }

        // Order: non-tripped providers first, then tripped (recovery pass).
        $live = [];
        $tripped = [];
        foreach ($configured as $p) {
            if ($this->isTripped($p)) {
                $tripped[] = $p;
            } else {
                $live[] = $p;
            }
        }
        $order = array_merge($live, $tripped);

        $errors = [];
        foreach ($order as $i => $provider) {
            try {
                $result = $call($provider);
                $this->resetCircuit($provider);

                return $result;
            } catch (BudgetExceededException $e) {
                // User-controlled cost cap — never silently fall through.
                throw $e;
            } catch (\Throwable $e) {
                $this->tripCircuit($provider);
                $msg = class_basename($provider).': '.$e->getMessage();
                $errors[] = $msg;
                Log::channel('ai')->warning('LLM provider failed; attempting fallback', [
                    'op' => $op,
                    'provider' => class_basename($provider),
                    'attempt' => $i + 1,
                    'chain_size' => count($order),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        throw new RuntimeException(
            'All LLM providers exhausted ('.count($order).' tried): '.implode(' | ', $errors)
        );
    }

    private function circuitKey(LlmInterface $provider): string
    {
        return 'llm.circuit.'.class_basename($provider);
    }

    private function isTripped(LlmInterface $provider): bool
    {
        return (bool) Cache::get($this->circuitKey($provider), false);
    }

    private function tripCircuit(LlmInterface $provider): void
    {
        Cache::put($this->circuitKey($provider), true, self::CIRCUIT_TTL_SECONDS);
    }

    private function resetCircuit(LlmInterface $provider): void
    {
        Cache::forget($this->circuitKey($provider));
    }
}
