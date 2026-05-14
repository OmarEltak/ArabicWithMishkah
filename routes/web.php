<?php

use App\Http\Controllers\BillingController;
use App\Http\Middleware\InternalHealthGate;
use App\Models\User;
use App\Services\Reports\HealthReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Cashier\Http\Controllers\WebhookController;

Route::view('/', 'welcome')->middleware('cache.public:900')->name('home');

/*
|--------------------------------------------------------------------------
| Programmatic SEO surface
|--------------------------------------------------------------------------
|
| Public, SEO-indexable pages built from the same product data that powers
| the authenticated app. Each page is a standalone landing optimised for a
| keyword cluster:
|
|   /jurisdictions/{iso}        — per-jurisdiction landing (EG, SA, AE, ...)
|   /glossary                   — bilingual legal-glossary index
|   /glossary/{iso}             — per-jurisdiction terminology page
|   /faq                        — FAQ with FAQPage structured data
|   /sitemap.xml                — dynamic, includes every public page
|
| All public routes inherit the welcome page's SEO scaffolding (canonical,
| hreflang, Open Graph, JSON-LD).
*/

// All public marketing pages are cacheable for ~15 minutes for guests.
// The middleware self-disables for authenticated requests so the
// "Sign in" / "Dashboard" header swap continues to work.
Route::middleware('cache.public:900')->group(function () {
    Route::get('/jurisdictions/{iso}', function (string $iso) {
        $iso = strtoupper($iso);
        $known = ['EG', 'SA', 'AE', 'KW', 'QA', 'BH', 'OM', 'JO', 'LB', 'TN', 'LY'];
        abort_unless(in_array($iso, $known, true), 404);

        return view('marketing.jurisdiction', ['iso' => $iso]);
    })->where('iso', '[A-Za-z]{2}')->name('marketing.jurisdiction');

    Route::view('/glossary', 'marketing.glossary-index')->name('marketing.glossary');

    Route::view('/contracts', 'marketing.contracts-index')->name('marketing.contracts');

    Route::get('/contracts/{type}', function (string $type) {
        $type = strtolower($type);
        $known = ['spa', 'mou', 'employment', 'services', 'nda', 'lease', 'distribution', 'shareholders'];
        abort_unless(in_array($type, $known, true), 404);

        return view('marketing.contract-type', ['type' => $type]);
    })->where('type', '[a-z\-]+')->name('marketing.contract-type');

    Route::get('/contracts/{type}/{iso}', function (string $type, string $iso) {
        $type = strtolower($type);
        $iso = strtoupper($iso);
        $knownTypes = ['spa', 'mou', 'employment', 'services', 'nda', 'lease', 'distribution', 'shareholders'];
        $knownIso = ['EG', 'SA', 'AE', 'KW', 'QA', 'BH', 'OM', 'JO', 'LB', 'TN', 'LY'];
        abort_unless(in_array($type, $knownTypes, true) && in_array($iso, $knownIso, true), 404);

        return view('marketing.contract-jurisdiction', ['type' => $type, 'iso' => $iso]);
    })->where('type', '[a-z\-]+')->where('iso', '[A-Za-z]{2}')->name('marketing.contract-jurisdiction');

    Route::get('/glossary/{iso}', function (string $iso) {
        $iso = strtolower($iso);
        $path = resource_path('legal/translation-glossary-'.$iso.'.md');
        abort_unless(is_file($path), 404);

        return view('marketing.glossary', ['iso' => strtoupper($iso), 'content' => file_get_contents($path)]);
    })->where('iso', '[A-Za-z]{2}')->name('marketing.glossary-jurisdiction');

    Route::view('/faq', 'marketing.faq')->name('marketing.faq');
    Route::view('/pricing', 'marketing.pricing')->name('marketing.pricing');

    Route::view('/help', 'help.index')->name('help.index');
    Route::get('/help/{slug}', function (string $slug) {
        return view('help.show', ['slug' => $slug]);
    })->where('slug', '[a-z0-9\-]+')->name('help.show');

    Route::view('/changelog', 'marketing.changelog')->name('changelog');
});

/*
|--------------------------------------------------------------------------
| Billing
|--------------------------------------------------------------------------
| Stripe checkout, customer portal, webhook. Falls back gracefully to a
| "billing not configured" view when STRIPE_SECRET / STRIPE_WEBHOOK_SECRET
| are not set, so dev environments don't see fatal errors.
| See docs/BILLING.md for the install + wire-up steps.
*/
Route::middleware('auth')->group(function () {
    Route::get('/billing/checkout/{plan}', [BillingController::class, 'checkout'])
        ->name('billing.checkout');
    Route::get('/billing/success', [BillingController::class, 'success'])
        ->name('billing.success');
    Route::get('/billing/portal', [BillingController::class, 'portal'])
        ->name('billing.portal');
});
// Webhook is public (Stripe needs to reach it). Cashier's WebhookController
// verifies the Stripe-Signature header against STRIPE_WEBHOOK_SECRET, handles
// idempotency, and dispatches a WebhookHandled event we listen to for our
// own plan-flip + audit-log side effects (see App\Listeners\StripeEventSubscriber).
// Rate-limited because the route is CSRF-free by necessity.
Route::post('/billing/webhook', [WebhookController::class, 'handleWebhook'])
    ->middleware('throttle:60,1')
    ->name('billing.webhook');

/*
|--------------------------------------------------------------------------
| Legal pages — public, indexable
|--------------------------------------------------------------------------
| Egypt-governed Terms, Privacy (Law 151/2020), DPA, AUP. Linked from
| the marketing footer and explicitly allowed in robots.txt.
*/
// Legal pages change rarely — cache for 1 hour for guests.
Route::middleware('cache.public:3600')->group(function () {
    Route::view('/legal/terms', 'legal.terms')->name('legal.terms');
    Route::view('/legal/privacy', 'legal.privacy')->name('legal.privacy');
    Route::view('/legal/dpa', 'legal.dpa')->name('legal.dpa');
    Route::view('/legal/aup', 'legal.aup')->name('legal.aup');
});

// Sitemap regenerates from DB; safe to cache for 1 hour at CDN edge.
Route::get('/sitemap.xml', function () {
    return response()->view('marketing.sitemap', [], 200, ['Content-Type' => 'application/xml']);
})->middleware('cache.public:3600')->name('sitemap');

/*
|--------------------------------------------------------------------------
| Internal health endpoint
|--------------------------------------------------------------------------
| Gated by InternalHealthGate (X-Internal-Health-Key header). Returns a
| JSON snapshot of corpus health, queue depth, audit-chain head integrity,
| and provider configuration. Designed to be polled by uptime monitors.
*/
Route::get('/__internal/health', function () {
    return response()->json(
        app(HealthReport::class)->generate(),
        200,
        ['Content-Type' => 'application/json'],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
    );
})->middleware([InternalHealthGate::class, 'throttle:120,1'])->name('internal.health');

// Dev-only auto-login. Lets browser automation (and quick QA cycles) bypass
// the Livewire login form. Refuses to run outside the local environment.
//
// Defence in depth: the outer `if` check is baked into a committed route
// cache (`php artisan route:cache`) if produced locally. The runtime
// `abort_unless` inside the closure prevents the route from ever
// authenticating against any non-local environment, regardless of how the
// route table was loaded.
if (app()->environment('local')) {
    Route::get('/__dev/login', function (Request $request) {
        abort_unless(app()->environment('local') && config('app.debug') === true, 404);

        $email = (string) $request->query('email', 'test@example.com');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response('invalid email', 400);
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            return response('user not found', 404);
        }
        Auth::login($user);
        $request->session()->regenerate();

        // Closed redirect — relative paths only, never an absolute URL or
        // scheme-relative URL (`//evil.example.com`). Prevents the dev
        // helper from doubling as an open-redirect gadget.
        $to = (string) $request->query('to', '/dashboard');
        if (! Str::startsWith($to, '/') || Str::startsWith($to, '//')) {
            $to = '/dashboard';
        }

        return redirect($to);
    });
}

// Locale switcher — sets the user's preferred locale (persisted on the
// users.locale column when authenticated, otherwise stored in session for
// guests). Accepts ?locale=en|ar; redirects back to the previous page.
Route::post('/locale', function (Request $request) {
    $locale = (string) $request->input('locale', 'en');
    if (! in_array($locale, ['en', 'ar'], true)) {
        $locale = 'en';
    }
    if ($user = $request->user()) {
        $user->forceFill(['locale' => $locale])->save();
    }
    $request->session()->put('locale', $locale);

    return back();
})->middleware('throttle:20,1')->name('locale.set');

Route::middleware(['auth', 'verified', 'throttle:120,1'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('lawyer/knowledge-base', 'pages::lawyer.knowledge-base')->name('lawyer.knowledge');
    Route::livewire('lawyer/law-search', 'pages::lawyer.law-search')->name('lawyer.law-search');
    Route::livewire('lawyer/law/{docId}', 'pages::lawyer.law-show')->name('lawyer.law-show');
    Route::livewire('lawyer/knowledge-base/{docId}/diff', 'pages::lawyer.document-diff')->name('lawyer.document-diff');
    Route::livewire('lawyer/chat', 'pages::lawyer.chat')->name('lawyer.chat');
    Route::livewire('lawyer/contracts', 'pages::lawyer.contracts')->name('lawyer.contracts');
    Route::livewire('lawyer/templates', 'pages::lawyer.templates')->name('lawyer.templates');
    Route::livewire('lawyer/usage', 'pages::lawyer.usage')->name('lawyer.usage');
    Route::livewire('lawyer/audit', 'pages::lawyer.audit')->name('lawyer.audit');
    Route::livewire('lawyer/clippings', 'pages::lawyer.clippings')->name('lawyer.clippings');
    Route::livewire('lawyer/matters', 'pages::lawyer.matters')->name('lawyer.matters');
});

require __DIR__.'/settings.php';
