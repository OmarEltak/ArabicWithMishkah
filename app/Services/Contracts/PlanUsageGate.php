<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Enforces per-plan monthly draft-count limits.
 *
 * Called from ContractDraftingService::startSession() BEFORE the LLM is
 * touched. If the user is over their plan cap, throws PlanLimitException
 * which the Volt component turns into a friendly toast.
 *
 * The counter rolls over monthly: the first draft attempt after a
 * calendar month has elapsed since `usage_period_start` resets the
 * counter to 0 and updates the period start.
 */
final class PlanUsageGate
{
    /**
     * Check + increment in one atomic step. Returns the current usage tuple
     * after the increment. Throws PlanLimitException when the user is over
     * their plan cap.
     *
     * @return array{used:int, limit:?int, plan:string}
     */
    public function recordDraftAttempt(User $user): array
    {
        return \DB::transaction(function () use ($user) {
            // Re-load with a row lock so two concurrent draft attempts can't
            // both pass the cap check.
            $u = User::query()->whereKey($user->id)->lockForUpdate()->first();
            if (! $u) {
                throw new \RuntimeException('User not found');
            }

            $this->maybeRollover($u);

            $plan = (string) ($u->plan ?? 'free');
            $limits = (array) config('lawyer.plan_limits', []);
            $cap = $limits[$plan]['drafts_per_month'] ?? null;
            $used = (int) ($u->drafts_used_this_period ?? 0);

            if ($cap !== null && $used >= $cap) {
                throw new PlanLimitException($plan, $used, $cap);
            }

            $u->drafts_used_this_period = $used + 1;
            $u->save();

            return [
                'used' => (int) $u->drafts_used_this_period,
                'limit' => $cap,
                'plan' => $plan,
            ];
        });
    }

    /**
     * Read-only inspection of the user's current usage. Does not mutate.
     *
     * @return array{used:int, limit:?int, plan:string, period_start:?string}
     */
    public function snapshot(User $user): array
    {
        $u = $user->fresh() ?? $user;
        $plan = (string) ($u->plan ?? 'free');
        $limits = (array) config('lawyer.plan_limits', []);

        return [
            'used' => (int) ($u->drafts_used_this_period ?? 0),
            'limit' => $limits[$plan]['drafts_per_month'] ?? null,
            'plan' => $plan,
            'period_start' => $u->usage_period_start?->toIso8601String(),
        ];
    }

    /**
     * Roll the counter over when a calendar month has elapsed since the
     * period started. The period is intentionally a rolling 30 days from
     * first-draft, not a calendar month, so a user who signs up on the
     * 28th doesn't only get 2 days of free tier before reset.
     */
    private function maybeRollover(User $u): void
    {
        $start = $u->usage_period_start ? Carbon::parse($u->usage_period_start) : null;
        if ($start === null) {
            $u->usage_period_start = now()->toDateString();

            return;
        }
        if ($start->diffInDays(now()) >= 30) {
            $u->drafts_used_this_period = 0;
            $u->usage_period_start = now()->toDateString();
        }
    }
}
