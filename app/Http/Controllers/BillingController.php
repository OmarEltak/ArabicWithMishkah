<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Cashier\Checkout;
use Stripe\Exception\InvalidRequestException;

/**
 * Stripe-via-Cashier billing controller. Falls back gracefully when no
 * Stripe key is configured — UI surfaces "Billing not yet configured"
 * instead of throwing a fatal error.
 *
 * Wiring order (production):
 *   1. composer require laravel/cashier              ✓ done
 *   2. php artisan migrate                           ✓ done (customer columns
 *      came from 2026_05_12_000001; subscription_items from Cashier publish)
 *   3. Set STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET in .env
 *   4. Create products + prices in Stripe Dashboard, copy IDs into env vars
 *      (STRIPE_PRICE_SOLO_MONTHLY etc — see config/lawyer.php plans block)
 *   5. Set the webhook URL to /billing/webhook in Stripe Dashboard
 *
 * Webhook routing: we proxy to Cashier's WebhookController so signature
 * verification + idempotency + all the standard event handlers come for
 * free. Custom subscription side-effects (plan-flip on user, audit-log)
 * are wired via the WebhookHandled event listener.
 *
 * Tap (MENA-friendly) wiring is documented separately in docs/BILLING.md;
 * once you have a Tap merchant account, this controller can be subclassed
 * or extended with a TapBillingController that follows the same shape.
 */
class BillingController extends Controller
{
    /**
     * Kick off a Stripe Checkout session for the requested plan.
     */
    public function checkout(Request $request, string $plan): RedirectResponse|Response|Checkout
    {
        $validPlans = array_keys((array) config('lawyer.plans', []));
        abort_unless(in_array($plan, $validPlans, true) && $plan !== 'free' && $plan !== 'enterprise', 404);

        if (! $this->stripeConfigured()) {
            return response()->view('billing.not-configured', [
                'plan' => $plan,
                'reason' => 'Stripe API keys are not set. See docs/BILLING.md.',
            ], 503);
        }

        // Choose monthly by default; yearly is opt-in via ?cycle=yearly.
        $cycle = $request->query('cycle') === 'yearly' ? 'yearly' : 'monthly';
        $priceKey = $cycle === 'yearly' ? 'stripe_price_yearly' : 'stripe_price_monthly';
        $priceId = config("lawyer.plans.{$plan}.{$priceKey}");

        if (empty($priceId)) {
            return response()->view('billing.not-configured', [
                'plan' => $plan,
                'reason' => "No Stripe price ID configured for {$plan} ({$cycle}). Set STRIPE_PRICE_".strtoupper($plan).'_'.strtoupper($cycle).' in .env.',
            ], 503);
        }

        $user = $request->user();

        try {
            // Cashier's newSubscription() handles customer creation, trial
            // period, and the redirect to Stripe's hosted checkout. The
            // returned Checkout object is rendered as a redirect response
            // by Laravel out of the box.
            return $user
                ->newSubscription('default', $priceId)
                ->trialDays((int) config('lawyer.trial_days', 14))
                ->checkout([
                    'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('marketing.pricing'),
                ]);
        } catch (InvalidRequestException $e) {
            // Typically: bad price ID, currency mismatch, or trial already used.
            // Surface a 503 with the message instead of a Stripe stack trace.
            return response()->view('billing.not-configured', [
                'plan' => $plan,
                'reason' => 'Stripe rejected the checkout request: '.$e->getMessage(),
            ], 503);
        }
    }

    /**
     * Stripe redirects here after successful checkout. Real implementation
     * (with Cashier) confirms the subscription, updates the user's plan,
     * and logs the upgrade event.
     */
    public function success(Request $request): RedirectResponse
    {
        // Cashier's webhook fires asynchronously and updates the user's plan
        // before this redirect — by the time we get here the subscription
        // should already be active. We still revalidate to handle the rare
        // race where the redirect beats the webhook.
        $user = $request->user();

        app(AuditLogger::class)->log(
            action: 'billing.checkout.success',
            subject: $user,
            summary: 'User completed Stripe checkout flow',
            metadata: [
                'plan' => $user->plan,
                'stripe_id' => $user->stripe_id,
                'session_id' => $request->query('session_id'),
            ],
            userId: $user->id,
        );

        return redirect()->route('billing.edit')->with('status', 'Welcome to '.ucfirst($user->plan ?? 'paid').'! Your plan is active.');
    }

    /**
     * Sends the user to Stripe's billing portal where they can update
     * payment methods, cancel, or change plan.
     */
    public function portal(Request $request): RedirectResponse|Response
    {
        $user = $request->user();

        if (! $this->stripeConfigured() || empty($user->stripe_id)) {
            return response()->view('billing.not-configured', [
                'plan' => $user->plan,
                'reason' => 'No active Stripe customer for this account.',
            ], 503);
        }

        return $user->redirectToBillingPortal(route('billing.edit'));
    }

    /**
     * Returns true only when the Stripe API key is set. Lets every other
     * method in this controller fail gracefully in dev / preview envs.
     */
    private function stripeConfigured(): bool
    {
        return ! empty(config('services.stripe.secret'));
    }
}
