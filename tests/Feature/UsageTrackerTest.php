<?php

declare(strict_types=1);

use App\Models\AiUsageEvent;
use App\Models\User;
use App\Services\AI\BudgetExceededException;
use App\Services\AI\UsageTracker;

beforeEach(function () {
    $this->rates = [
        'anthropic.claude-sonnet-4-6' => ['input' => 3.00, 'output' => 15.00],
        'anthropic' => ['input' => 3.00, 'output' => 15.00],
        'voyage' => ['input' => 0.06, 'output' => 0.0],
    ];
});

it('computes cost in micro-USD for an Anthropic call', function () {
    $tracker = new UsageTracker(dailyBudgetUsd: 50, perUserDailyBudgetUsd: 5, rates: $this->rates);

    $event = $tracker->record(
        provider: 'anthropic',
        operation: 'chat',
        model: 'claude-sonnet-4-6',
        inputTokens: 1_000_000,
        outputTokens: 500_000,
    );

    // 1M input × $3 + 500k output × $15 = $3 + $7.50 = $10.50 → 10_500_000 micros
    expect($event->cost_micros)->toBe(10_500_000);
});

it('throws BudgetExceededException when global daily cap is hit', function () {
    $tracker = new UsageTracker(dailyBudgetUsd: 0.01, perUserDailyBudgetUsd: 100, rates: $this->rates);

    // Record an event that exceeds the cap.
    $tracker->record(
        provider: 'anthropic',
        operation: 'chat',
        model: 'claude-sonnet-4-6',
        inputTokens: 100_000,
        outputTokens: 0,
    );

    $tracker->assertWithinBudget();
})->throws(BudgetExceededException::class);

it('throws when per-user cap is exceeded but global is fine', function () {
    $tracker = new UsageTracker(dailyBudgetUsd: 1000, perUserDailyBudgetUsd: 0.01, rates: $this->rates);
    $user = User::factory()->create();

    AiUsageEvent::create([
        'user_id' => $user->id,
        'provider' => 'anthropic',
        'operation' => 'chat',
        'model' => 'claude-sonnet-4-6',
        'cost_micros' => 1_000_000, // $1 — exceeds 1¢ cap
    ]);

    $tracker->assertWithinBudget(userId: $user->id);
})->throws(BudgetExceededException::class);

it('dashboard returns aggregated spend', function () {
    $tracker = new UsageTracker(dailyBudgetUsd: 50, perUserDailyBudgetUsd: 5, rates: $this->rates);

    AiUsageEvent::create([
        'user_id' => null,
        'provider' => 'voyage',
        'operation' => 'embed',
        'model' => 'voyage-3',
        'input_tokens' => 1000,
        'cost_micros' => 60_000, // 6¢
    ]);

    $d = $tracker->dashboard();

    expect($d['today_global_usd'])->toBeGreaterThan(0);
    expect($d['today_global_budget_usd'])->toBe(50.0);
    expect($d['recent'])->toBeArray();
    expect(count($d['recent']))->toBeGreaterThan(0);
});
