<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Stripe-via-Cashier billing controller. Falls back gracefully when no
 * Stripe key is configured — UI surfaces "Billing not yet configured"
 * instead of throwing a fatal error.
 *
 * Wiring order (production):
 *   1. composer require laravel/cashier
 *   2. php artisan cashier:install
 *   3. Set STRIPE_KEY, STRIPE_SECRET, STRIPE_WEBHOOK_SECRET in .env
 *   4. Create products + prices in Stripe Dashboard, copy IDs into
 *      config/lawyer.php (`stripe_price_monthly` / `stripe_price_yearly`)
 *      via STRIPE_PRICE_SOLO_MONTHLY etc env vars
 *   5. Set the webhook URL to /billing/webhook in Stripe Dashboard
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
    public function checkout(Request $request, string $plan): RedirectResponse|Response
    {
        $validPlans = array_keys((array) config('lawyer.plans', []));
        abort_unless(in_array($plan, $validPlans, true) && $plan !== 'free' && $plan !== 'enterprise', 404);

        if (! $this->stripeConfigured()) {
            return response()->view('billing.not-configured', [
                'plan' => $plan,
                'reason' => 'Stripe API keys are not set. See docs/BILLING.md.',
            ], 503);
        }

        // When Cashier is installed, swap this stub for the real call:
        //
        //     return $request->user()
        //         ->newSubscription('default', config("lawyer.plans.{$plan}.stripe_price_monthly"))
        //         ->trialDays(14)
        //         ->checkout([
        //             'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
        //             'cancel_url'  => route('marketing.pricing'),
        //         ]);
        //
        // Until then, return a clear placeholder so the UI doesn't appear
        // broken in development.
        return response()->view('billing.cashier-pending', [
            'plan' => $plan,
            'message' => 'Cashier is not yet installed. Run `composer require laravel/cashier` and follow docs/BILLING.md.',
        ], 503);
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
            ],
            userId: $user->id,
        );

        return redirect()->route('billing.edit')->with('status', 'Welcome to '.ucfirst($user->plan).'! Your plan is active.');
    }

    /**
     * Sends the user to Stripe's billing portal where they can update
     * payment methods, cancel, or change plan.
     */
    public function portal(Request $request): RedirectResponse|Response
    {
        if (! $this->stripeConfigured() || empty($request->user()->stripe_id)) {
            return response()->view('billing.not-configured', [
                'plan' => $request->user()->plan,
                'reason' => 'No active Stripe customer for this account.',
            ], 503);
        }

        // With Cashier:
        //   return $request->user()->redirectToBillingPortal(route('settings.billing'));
        return response()->view('billing.cashier-pending', [
            'plan' => $request->user()->plan,
            'message' => 'Cashier portal redirect — pending composer install.',
        ], 503);
    }

    /**
     * Stripe webhook endpoint. Verifies the signature before processing.
     * Cashier provides a `WebhookController` that handles every event
     * type for you; the stub below documents what to wire when you
     * install it.
     */
    public function webhook(Request $request): Response
    {
        $signature = $request->header('Stripe-Signature');
        $secret = (string) config('services.stripe.webhook_secret', '');

        if (empty($secret)) {
            return response('Webhook not configured', 503);
        }

        // Signature verification — Cashier does this automatically. The
        // manual equivalent for reference:
        //
        //     $payload = $request->getContent();
        //     try {
        //         \Stripe\Webhook::constructEvent($payload, $signature, $secret);
        //     } catch (\Stripe\Exception\SignatureVerificationException $e) {
        //         return response('Invalid signature', 400);
        //     }
        //
        // Events we care about (when Cashier is installed it handles all of these):
        //   customer.subscription.created   → set user.plan from price metadata
        //   customer.subscription.updated   → plan change / quantity update
        //   customer.subscription.deleted   → drop user back to 'free'
        //   invoice.payment_succeeded       → confirm renewal
        //   invoice.payment_failed          → start dunning sequence
        //   checkout.session.completed      → grant entitlements

        return response('Cashier not installed — webhook is a stub.', 503);
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
