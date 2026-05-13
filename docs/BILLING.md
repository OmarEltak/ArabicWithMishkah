# Billing Setup Guide

> Read after `docs/DEPLOY.md`. This guide gets billing from "scaffold present
> but stripe_configured = false" to "Solo and Firm customers can actually pay."

The app already ships with:

- `/pricing` (public marketing page) — renders the 4 plans from `config/lawyer.php`
- `/settings/billing` (authenticated) — shows current plan + usage + upgrade button
- `App\Http\Controllers\BillingController` with stub methods for checkout, success, portal, webhook
- `App\Services\Contracts\PlanUsageGate` — enforces draft caps per plan (already active)
- `subscriptions` table + `users.stripe_id` etc. (Cashier-compatible schema)
- Webhook URI `/billing/webhook` excluded from CSRF middleware

What you need to wire to make payments actually work:

## 1. Install Laravel Cashier (5 min)

```bash
composer require laravel/cashier
php artisan vendor:publish --tag="cashier-migrations"
```

**Drop our stand-in `subscriptions` table** (Cashier owns it; ours is a placeholder):

```bash
php artisan tinker --execute='Schema::dropIfExists("subscriptions");'
php artisan migrate
```

Then add the `Billable` trait to `App\Models\User`:

```php
use Laravel\Cashier\Billable;

class User extends Authenticatable {
    use Billable;
    // ... existing code
}
```

## 2. Create the Stripe products (15 min)

In the Stripe Dashboard (`https://dashboard.stripe.com/products`):

| Product | Recurring monthly | Recurring yearly |
|---|---|---|
| Solo  | EGP 2,400/month | EGP 24,000/year |
| Firm  | EGP 7,900/month | EGP 79,000/year |
| Enterprise | (don't create — handled by sales contract) | — |

For each product, create a **Price** in EGP for both monthly and yearly cadences.
Copy the Price ID (`price_xxx...`) for each one.

> **Egypt note**: Stripe accepts EGP, but settlement may route through USD
> depending on your Stripe account jurisdiction. For Egyptian customers using
> local cards, see step 7 (Tap integration).

## 3. Set the .env keys

```env
STRIPE_KEY=pk_test_...                       # publishable key
STRIPE_SECRET=sk_test_...                    # secret key
STRIPE_WEBHOOK_SECRET=whsec_...              # set in step 5

STRIPE_PRICE_SOLO_MONTHLY=price_solo_monthly_xxx
STRIPE_PRICE_SOLO_YEARLY=price_solo_yearly_xxx
STRIPE_PRICE_FIRM_MONTHLY=price_firm_monthly_xxx
STRIPE_PRICE_FIRM_YEARLY=price_firm_yearly_xxx

CASHIER_CURRENCY=egp                         # invoice currency
CASHIER_CURRENCY_LOCALE=ar_EG                # invoice locale
```

Verify with `php artisan tinker`:

```php
echo config('services.stripe.secret') ? 'ok' : 'missing';
echo config('lawyer.plans.solo.stripe_price_monthly');
```

## 4. Swap the stub for real Cashier calls

Open `app/Http/Controllers/BillingController.php`. In the `checkout()` method, replace
the placeholder `return response()->view('billing.cashier-pending', ...)` block with:

```php
return $request->user()
    ->newSubscription('default', config("lawyer.plans.{$plan}.stripe_price_monthly"))
    ->trialDays(14)
    ->checkout([
        'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'  => route('marketing.pricing'),
    ]);
```

And in `portal()`:

```php
return $request->user()->redirectToBillingPortal(route('settings.billing'));
```

Cashier's `WebhookController` handles every event for you — replace our
`webhook()` route with Cashier's. In `routes/web.php`:

```php
// Replace:
//   Route::post('/billing/webhook', [BillingController::class, 'webhook'])
//        ->name('billing.webhook');
// With:
   Route::post('/billing/webhook', '\Laravel\Cashier\Http\Controllers\WebhookController')
        ->name('billing.webhook');
```

## 5. Wire the webhook (10 min)

In the Stripe Dashboard → Developers → Webhooks → Add endpoint:

- URL: `https://my-lawyer.com/billing/webhook`
- Events to listen for:
  - `customer.subscription.created`
  - `customer.subscription.updated`
  - `customer.subscription.deleted`
  - `invoice.payment_action_required`
  - `invoice.payment_succeeded`
  - `invoice.payment_failed`
  - `checkout.session.completed`

Copy the signing secret (`whsec_...`) into `.env` as `STRIPE_WEBHOOK_SECRET`.

## 6. Wire user.plan to subscription state

The free-tier `PlanUsageGate` reads `User->plan` directly. Once Cashier is
installed, you'd want `User->plan` to reflect the user's current Stripe
subscription. Add a model hook in `App\Models\User`:

```php
public function getPlanAttribute($value): string
{
    // Cashier-aware: prefer subscription state over the plain column.
    $sub = $this->subscriptions()->where('stripe_status', 'active')->latest()->first();
    if (! $sub) {
        return $value ?? 'free';
    }
    foreach (config('lawyer.plans', []) as $slug => $meta) {
        if (in_array($sub->stripe_price, [
            $meta['stripe_price_monthly'] ?? null,
            $meta['stripe_price_yearly'] ?? null,
        ], true)) {
            return $slug;
        }
    }
    return $value ?? 'free';
}
```

Now `PlanUsageGate::snapshot()` and `/settings/billing` automatically reflect
the real plan from Stripe.

## 7. Tap integration (Egypt-local card processing)

Stripe is great globally; **Tap** (https://www.tap.company) is the
Egypt-friendly path for Mada / Meeza local cards + Fawry voucher payment.
You ideally support both — Stripe for international customers, Tap for
local Egyptian customers (most law-firm clients).

Tap doesn't have a Laravel package as mature as Cashier. The integration
shape:

```php
// app/Http/Controllers/TapBillingController.php
public function checkout(Request $request, string $plan)
{
    $tap = new \Tap\Charges(env('TAP_SECRET_KEY'));
    $charge = $tap->create([
        'amount' => config("lawyer.plans.{$plan}.price_monthly_egp"),
        'currency' => 'EGP',
        'customer' => [
            'first_name' => $request->user()->name,
            'email' => $request->user()->email,
        ],
        'source' => ['id' => 'src_card'],
        'redirect' => ['url' => route('billing.tap-success')],
        'post' => ['url' => route('billing.tap-webhook')],
    ]);
    return redirect($charge->transaction->url);
}
```

Tap supports:
- Visa, Mastercard, AMEX
- Mada (Saudi local)
- Meeza (Egyptian local)
- KNET (Kuwaiti local)
- Apple Pay / Google Pay

Tap webhook URI is already excluded from CSRF (`billing/tap-webhook` in
`bootstrap/app.php`).

For first-launch in Egypt, **start with Stripe only**; add Tap once you have
your first 10 paying customers and conversion data.

## 8. Pricing changes in production

Editing `config/lawyer.php` prices doesn't change what existing customers
pay — that's set in their Stripe subscription. When you change a price:

1. **For new customers**: just update `STRIPE_PRICE_SOLO_MONTHLY` etc. in `.env`
   to a new Price ID, deploy. New checkouts use the new price.
2. **For existing customers**: choose between
   - Letting them stay on the old price (most common — Stripe calls this
     "grandfathering")
   - Migrating them via `$subscription->swap($newPriceId)`. Adds a prorated
     credit/charge on next invoice.

Document any pricing change in `docs/CHANGELOG.md` for legal traceability.

## 9. Dunning (failed payment recovery)

Cashier ships dunning behavior out of the box:
- `invoice.payment_failed` triggers a retry per the Stripe retry policy
- After ~3 failed retries, subscription moves to `past_due` then `unpaid`

To customize the email sent on failure:

```php
// AppServiceProvider::boot()
Cashier::useInvoicePdfBuilder('app');
```

A minimal "your payment failed" template lives at
`resources/views/emails/billing/payment-failed.blade.php` — write it
when you wire this up.

## 10. Refunds + cancellations

Cashier handles both via Stripe's API:

```php
// Cancel at period end
$user->subscription('default')->cancel();

// Cancel immediately
$user->subscription('default')->cancelNow();

// Refund last invoice
$user->refund($invoiceId, ['reason' => 'requested_by_customer']);
```

Customer-initiated cancellation works via the Stripe billing portal at
`route('billing.portal')` — no code needed.

## 11. Testing in development

Use Stripe's test cards:

| Card | Outcome |
|---|---|
| `4242 4242 4242 4242` | Success |
| `4000 0000 0000 9995` | Insufficient funds |
| `4000 0025 0000 3155` | Requires 3DS authentication |
| `4000 0000 0000 0341` | Attaching card succeeds but charge fails |

Use Stripe CLI for webhook testing locally:

```bash
stripe listen --forward-to localhost:8000/billing/webhook
# Copy the whsec_... it prints into .env as STRIPE_WEBHOOK_SECRET
stripe trigger checkout.session.completed
```

## 12. Going live — flip from test mode

Once production webhook is verified working with test cards:

1. Switch `STRIPE_KEY` / `STRIPE_SECRET` from `pk_test_…`/`sk_test_…` to
   live keys
2. Create live versions of every Price (test and live are separate spaces)
3. Update `STRIPE_PRICE_*` env vars to the live Price IDs
4. Create a live-mode webhook endpoint in Stripe Dashboard, point at
   `https://my-lawyer.com/billing/webhook`, copy its `whsec_…` to
   `STRIPE_WEBHOOK_SECRET`
5. Smoke-test with a real card for EGP 1 (then refund it)
6. Announce.

---

## Compliance footnotes

- **VAT** — Egyptian VAT is 14% on services. Set `CASHIER_CURRENCY=egp` and
  configure Stripe Tax to compute VAT on EGP-denominated invoices.
- **Invoicing language** — set `CASHIER_CURRENCY_LOCALE=ar_EG` so invoices
  render in Egyptian Arabic.
- **Receipts retention** — Egyptian Commercial Code Art. 47 requires
  7-year retention of commercial books. Stripe retains invoices
  indefinitely; export quarterly for redundancy.
- **PCI** — Cashier + Stripe Checkout means you never touch raw card
  data. PCI scope is reduced to "Self-Assessment Questionnaire A" only.
