<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookHandled;
use Laravel\Cashier\Events\WebhookReceived;

/**
 * Subscribes to Cashier's webhook events and applies our domain side-effects:
 *
 *   - User.plan is the source of truth for feature gating (see PlanUsageGate).
 *     Cashier's `subscriptions` table tracks Stripe state; we mirror the
 *     active plan onto the user row so feature checks don't have to join.
 *   - AuditLog gets an append-only entry for every event we care about,
 *     so finance / support can answer "why is this user on the X plan?".
 *   - drafts_used_this_period is reset on plan change so the customer
 *     gets a clean usage period after an upgrade/downgrade.
 *
 * Cashier itself updates the `subscriptions` and `subscription_items`
 * tables before we run — we read from them, we don't write to them here.
 */
class StripeEventSubscriber
{
    /**
     * Cashier fires this BEFORE its own handlers run. We use it to log
     * raw event arrivals for diagnostics (rare — most teams only care
     * about WebhookHandled). Cheap to leave wired; toggle off by removing
     * the subscribe() registration below if it gets too chatty.
     */
    public function handleReceived(WebhookReceived $event): void
    {
        $type = $event->payload['type'] ?? 'unknown';
        Log::channel('errors')->info('Stripe webhook received', ['type' => $type]);
    }

    /**
     * Fires AFTER Cashier has applied its built-in handler (subscription
     * row created/updated/deleted, customer columns updated, etc.). This
     * is where we layer on app-specific behaviour.
     */
    public function handleHandled(WebhookHandled $event): void
    {
        $type = $event->payload['type'] ?? '';
        $data = $event->payload['data']['object'] ?? [];

        match ($type) {
            'customer.subscription.created',
            'customer.subscription.updated' => $this->onSubscriptionActive($data),
            'customer.subscription.deleted' => $this->onSubscriptionEnded($data),
            'invoice.payment_failed' => $this->onPaymentFailed($data),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $data  Stripe Subscription object.
     */
    private function onSubscriptionActive(array $data): void
    {
        $stripeCustomerId = (string) ($data['customer'] ?? '');
        $user = User::query()->where('stripe_id', $stripeCustomerId)->first();
        if (! $user) {
            return;
        }

        $status = (string) ($data['status'] ?? '');
        $priceId = (string) ($data['items']['data'][0]['price']['id'] ?? '');

        $plan = $this->planFromPriceId($priceId);
        if ($plan === null) {
            // Unknown price ID — surface so we can add it to config/lawyer.php.
            Log::channel('errors')->warning('Stripe price ID has no matching plan in config/lawyer.php', [
                'user_id' => $user->id,
                'stripe_id' => $stripeCustomerId,
                'price_id' => $priceId,
            ]);

            return;
        }

        $previousPlan = (string) ($user->plan ?? 'free');
        $user->plan = $plan;

        // Reset usage on upgrade/downgrade so a customer who upgrades
        // mid-month gets the new cap from "right now", not pro-rated.
        if ($previousPlan !== $plan) {
            $user->drafts_used_this_period = 0;
            $user->usage_period_start = Carbon::now()->toDateString();
        }
        $user->save();

        app(AuditLogger::class)->log(
            action: $previousPlan !== $plan ? 'billing.plan.changed' : 'billing.subscription.renewed',
            subject: $user,
            summary: $previousPlan !== $plan
                ? sprintf('Plan changed: %s → %s', $previousPlan, $plan)
                : sprintf('Subscription renewed on %s', $plan),
            metadata: [
                'from_plan' => $previousPlan,
                'to_plan' => $plan,
                'stripe_status' => $status,
                'price_id' => $priceId,
            ],
            userId: $user->id,
        );
    }

    /**
     * @param  array<string, mixed>  $data  Stripe Subscription object.
     */
    private function onSubscriptionEnded(array $data): void
    {
        $stripeCustomerId = (string) ($data['customer'] ?? '');
        $user = User::query()->where('stripe_id', $stripeCustomerId)->first();
        if (! $user) {
            return;
        }

        $previousPlan = (string) ($user->plan ?? 'free');
        $user->plan = 'free';
        $user->drafts_used_this_period = 0;
        $user->usage_period_start = Carbon::now()->toDateString();
        $user->save();

        app(AuditLogger::class)->log(
            action: 'billing.plan.cancelled',
            subject: $user,
            summary: sprintf('Subscription ended; reverted to free (was %s)', $previousPlan),
            metadata: ['from_plan' => $previousPlan],
            userId: $user->id,
        );
    }

    /**
     * @param  array<string, mixed>  $data  Stripe Invoice object.
     */
    private function onPaymentFailed(array $data): void
    {
        $stripeCustomerId = (string) ($data['customer'] ?? '');
        $user = User::query()->where('stripe_id', $stripeCustomerId)->first();
        if (! $user) {
            return;
        }

        app(AuditLogger::class)->log(
            action: 'billing.payment.failed',
            subject: $user,
            summary: sprintf('Invoice payment failed (attempt %d)', (int) ($data['attempt_count'] ?? 1)),
            metadata: [
                'invoice_id' => $data['id'] ?? null,
                'amount_due' => $data['amount_due'] ?? null,
                'currency' => $data['currency'] ?? null,
                'next_payment_attempt' => $data['next_payment_attempt'] ?? null,
            ],
            userId: $user->id,
        );

        // Plan stays as-is — Stripe handles dunning and will eventually
        // emit customer.subscription.deleted if all retries fail.
    }

    /**
     * Reverse-lookup a Stripe price ID against the config/lawyer.php plan
     * matrix. Returns the plan slug or null if not found.
     */
    private function planFromPriceId(string $priceId): ?string
    {
        if ($priceId === '') {
            return null;
        }

        foreach ((array) config('lawyer.plans', []) as $slug => $plan) {
            if (($plan['stripe_price_monthly'] ?? null) === $priceId) {
                return (string) $slug;
            }
            if (($plan['stripe_price_yearly'] ?? null) === $priceId) {
                return (string) $slug;
            }
        }

        return null;
    }

    /**
     * Register the listeners. The subscribe() pattern keeps both handler
     * methods on one class so plan-flip logic isn't split across files.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(WebhookReceived::class, [self::class, 'handleReceived']);
        $events->listen(WebhookHandled::class, [self::class, 'handleHandled']);
    }
}
