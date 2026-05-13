<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\AiUsageEvent;
use Illuminate\Support\Facades\Auth;

/**
 * Records every AI/external API call as a row in ai_usage_events with the
 * computed cost in micro-USD, and refuses to authorize new calls when a user
 * has exceeded their daily cap.
 *
 * Two responsibilities, kept together because they share the same source of
 * truth (the events table):
 *
 *   1. RECORDING — call ::record() after every API call. The hot path is
 *      AnthropicService.chat() and EmbeddingService.embedBatch().
 *   2. ENFORCEMENT — call ::assertWithinBudget() BEFORE expensive calls. Throws
 *      a BudgetExceededException that the caller can convert into a polite
 *      user-facing message.
 *
 * Pricing tables live in config/lawyer.php so they can be edited without code
 * changes; cost is locked in at write time.
 */
class UsageTracker
{
    public function __construct(
        private readonly float $dailyBudgetUsd,
        private readonly float $perUserDailyBudgetUsd,
        /** @var array<string, array<string, float>> */
        private readonly array $rates,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            dailyBudgetUsd: (float) config('lawyer.daily_budget_usd', 50.0),
            perUserDailyBudgetUsd: (float) config('lawyer.per_user_daily_budget_usd', 5.0),
            rates: config('lawyer.pricing', self::defaultRates()),
        );
    }

    public function record(
        string $provider,
        string $operation,
        ?string $model,
        ?int $inputTokens,
        ?int $outputTokens,
        ?int $userId = null,
        string $status = 'success',
        ?string $errorMessage = null,
        ?int $cacheReadTokens = null,
        ?int $cacheCreationTokens = null,
        array $metadata = [],
    ): AiUsageEvent {
        $costMicros = $this->computeCostMicros(
            provider: $provider,
            model: $model,
            inputTokens: $inputTokens ?? 0,
            outputTokens: $outputTokens ?? 0,
            cacheReadTokens: $cacheReadTokens ?? 0,
            cacheCreationTokens: $cacheCreationTokens ?? 0,
        );

        return AiUsageEvent::create([
            'user_id' => $userId ?? Auth::id(),
            'provider' => $provider,
            'operation' => $operation,
            'model' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'cache_read_tokens' => $cacheReadTokens,
            'cache_creation_tokens' => $cacheCreationTokens,
            'cost_micros' => $costMicros,
            'status' => $status,
            'error_message' => $errorMessage,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Throws if the caller has exceeded today's budget. The check is cheap
     * (single SUM aggregate) and called BEFORE every expensive API call.
     */
    public function assertWithinBudget(?int $userId = null): void
    {
        $userId = $userId ?? Auth::id();

        $today = AiUsageEvent::query()
            ->whereDate('created_at', today());

        $globalSpend = (clone $today)->sum('cost_micros') / 1_000_000;
        if ($globalSpend >= $this->dailyBudgetUsd) {
            throw new BudgetExceededException(
                "Daily global budget exceeded ($".number_format($globalSpend, 4)." / $".number_format($this->dailyBudgetUsd, 2).")."
            );
        }

        if ($userId !== null) {
            $userSpend = (clone $today)->where('user_id', $userId)->sum('cost_micros') / 1_000_000;
            if ($userSpend >= $this->perUserDailyBudgetUsd) {
                throw new BudgetExceededException(
                    "Your daily AI budget is exhausted ($".number_format($userSpend, 4)." / $".number_format($this->perUserDailyBudgetUsd, 2)."). Try again tomorrow or contact admin."
                );
            }
        }
    }

    /**
     * @return array{
     *   today_global_usd:float, today_global_budget_usd:float,
     *   today_user_usd:float, today_user_budget_usd:float,
     *   month_global_usd:float, recent:array<int, array<string, mixed>>,
     * }
     */
    public function dashboard(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();

        $todayGlobal = AiUsageEvent::whereDate('created_at', today())->sum('cost_micros') / 1_000_000;
        $todayUser = $userId ? AiUsageEvent::where('user_id', $userId)->whereDate('created_at', today())->sum('cost_micros') / 1_000_000 : 0.0;
        $monthGlobal = AiUsageEvent::whereBetween('created_at', [now()->startOfMonth(), now()])->sum('cost_micros') / 1_000_000;

        $recent = AiUsageEvent::query()
            ->latest('id')
            ->limit(20)
            ->get([
                'id', 'user_id', 'provider', 'operation', 'model',
                'input_tokens', 'output_tokens', 'cost_micros', 'status', 'created_at',
            ])
            ->map(fn ($e) => [
                'id' => $e->id,
                'when' => $e->created_at?->diffForHumans(),
                'provider' => $e->provider,
                'op' => $e->operation,
                'model' => $e->model,
                'in' => $e->input_tokens,
                'out' => $e->output_tokens,
                'cost' => $e->cost_micros / 1_000_000,
                'status' => $e->status,
            ])
            ->all();

        return [
            'today_global_usd' => $todayGlobal,
            'today_global_budget_usd' => $this->dailyBudgetUsd,
            'today_user_usd' => $todayUser,
            'today_user_budget_usd' => $this->perUserDailyBudgetUsd,
            'month_global_usd' => $monthGlobal,
            'recent' => $recent,
        ];
    }

    private function computeCostMicros(
        string $provider,
        ?string $model,
        int $inputTokens,
        int $outputTokens,
        int $cacheReadTokens,
        int $cacheCreationTokens,
    ): int {
        $key = $provider.($model ? '.'.$model : '');
        $rate = $this->rates[$key] ?? $this->rates[$provider] ?? null;
        if (! is_array($rate)) {
            return 0;
        }

        // Rates are USD per 1M tokens.
        $micros = 0;
        $micros += (int) round($inputTokens * (($rate['input'] ?? 0) / 1_000_000) * 1_000_000);
        $micros += (int) round($outputTokens * (($rate['output'] ?? 0) / 1_000_000) * 1_000_000);
        $micros += (int) round($cacheReadTokens * (($rate['cache_read'] ?? 0) / 1_000_000) * 1_000_000);
        $micros += (int) round($cacheCreationTokens * (($rate['cache_creation'] ?? 0) / 1_000_000) * 1_000_000);

        return max(0, $micros);
    }

    /**
     * @return array<string, array<string, float>>
     */
    public static function defaultRates(): array
    {
        // USD per 1M tokens. Update from anthropic.com/pricing & voyageai.com/pricing.
        return [
            'anthropic.claude-sonnet-4-6' => ['input' => 3.00, 'output' => 15.00, 'cache_read' => 0.30, 'cache_creation' => 3.75],
            'anthropic.claude-opus-4-7' => ['input' => 15.00, 'output' => 75.00, 'cache_read' => 1.50, 'cache_creation' => 18.75],
            'anthropic.claude-haiku-4-5-20251001' => ['input' => 0.80, 'output' => 4.00, 'cache_read' => 0.08, 'cache_creation' => 1.00],
            'anthropic' => ['input' => 3.00, 'output' => 15.00], // safe default
            'voyage.voyage-3' => ['input' => 0.06, 'output' => 0.0],
            'voyage' => ['input' => 0.06, 'output' => 0.0],
            'openai.text-embedding-3-small' => ['input' => 0.02, 'output' => 0.0],
            'openai' => ['input' => 0.02, 'output' => 0.0],
        ];
    }
}
