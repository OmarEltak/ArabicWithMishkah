<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Contracts\PlanLimitException;
use App\Services\Contracts\PlanUsageGate;

it('allows up to the plan cap and blocks the next attempt', function () {
    config()->set('lawyer.plan_limits', [
        'free' => ['drafts_per_month' => 3],
    ]);

    $user = User::factory()->create(['plan' => 'free']);
    $gate = new PlanUsageGate();

    expect($gate->recordDraftAttempt($user)['used'])->toBe(1);
    expect($gate->recordDraftAttempt($user)['used'])->toBe(2);
    expect($gate->recordDraftAttempt($user)['used'])->toBe(3);

    expect(fn () => $gate->recordDraftAttempt($user))
        ->toThrow(PlanLimitException::class);
});

it('treats null cap as unlimited', function () {
    config()->set('lawyer.plan_limits', [
        'enterprise' => ['drafts_per_month' => null],
    ]);
    $user = User::factory()->create(['plan' => 'enterprise']);
    $gate = new PlanUsageGate();

    for ($i = 1; $i <= 50; $i++) {
        $gate->recordDraftAttempt($user);
    }
    expect($user->fresh()->drafts_used_this_period)->toBe(50);
});

it('resets the counter after 30 days', function () {
    config()->set('lawyer.plan_limits', [
        'free' => ['drafts_per_month' => 3],
    ]);
    $user = User::factory()->create([
        'plan' => 'free',
        'drafts_used_this_period' => 3,
        'usage_period_start' => now()->subDays(31)->toDateString(),
    ]);
    $gate = new PlanUsageGate();

    $result = $gate->recordDraftAttempt($user);
    expect($result['used'])->toBe(1); // reset to 0, then +1
});

it('exposes plan-aware snapshot for UI', function () {
    config()->set('lawyer.plan_limits', [
        'solo' => ['drafts_per_month' => 25],
    ]);
    $user = User::factory()->create([
        'plan' => 'solo',
        'drafts_used_this_period' => 7,
    ]);
    $gate = new PlanUsageGate();

    $snap = $gate->snapshot($user);
    expect($snap['plan'])->toBe('solo');
    expect($snap['used'])->toBe(7);
    expect($snap['limit'])->toBe(25);
});
