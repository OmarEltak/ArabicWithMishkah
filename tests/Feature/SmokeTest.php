<?php

declare(strict_types=1);

use App\Models\Contract;
use App\Models\User;

/**
 * Smoke tests — fast, no-LLM regression guards that exercise every page
 * a real user touches. Designed to catch:
 *
 *   - A blade view that fails to compile on a fresh DB
 *   - A route that 500s because of a missing computed property
 *   - A model the dashboard widget reads that's been renamed
 *   - A middleware that throws when env keys are absent
 *
 * If any of these tests fail, NO user can use the site — the bar for
 * "ship-ready" is "every test in this file passes."
 *
 * Deliberately does NOT test the full AI/RAG drafting flow — that's
 * `tests/Feature/DraftingFlowTest.php`. Smoke is about page renders
 * and routing, not behavioural correctness of the AI loop.
 */

/* ─────────────────  Public marketing pages  ───────────────── */

it('renders the home page', function () {
    $this->get('/')->assertOk()->assertSee('My-lawyer');
});

it('renders the pricing page', function () {
    $this->get('/pricing')->assertOk();
});

it('renders the FAQ page', function () {
    $this->get('/faq')->assertOk();
});

it('renders all 11 jurisdiction landing pages', function () {
    foreach (['EG', 'SA', 'AE', 'KW', 'QA', 'BH', 'OM', 'JO', 'LB', 'TN', 'LY'] as $iso) {
        $this->get('/jurisdictions/'.$iso)->assertOk();
    }
});

it('renders the contracts index', function () {
    $this->get('/contracts')->assertOk();
});

it('renders all four legal pages', function () {
    $this->get('/legal/terms')->assertOk();
    $this->get('/legal/privacy')->assertOk();
    $this->get('/legal/dpa')->assertOk();
    $this->get('/legal/aup')->assertOk();
});

it('renders the sitemap with the right content-type', function () {
    $r = $this->get('/sitemap.xml');
    $r->assertOk();
    expect($r->headers->get('Content-Type'))->toContain('application/xml');
});

/* ─────────────────  Error pages  ───────────────── */

it('renders the 404 page on an unknown URL', function () {
    $r = $this->get('/this-path-definitely-does-not-exist-xyz');
    $r->assertNotFound();
    $r->assertSee('My-lawyer'); // branded, not a default Symfony page
});

it('renders the 404 on an unknown jurisdiction', function () {
    $this->get('/jurisdictions/ZZ')->assertNotFound();
});

/* ─────────────────  Auth gating  ───────────────── */

it('redirects guests away from the dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('redirects guests away from lawyer/chat', function () {
    $this->get('/lawyer/chat')->assertRedirect('/login');
});

/* ─────────────────  Authenticated app surface  ───────────────── */

it('renders the dashboard for a fresh user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user)->get('/dashboard')->assertOk();
});

it('renders the chat page for a fresh user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user)->get('/lawyer/chat')->assertOk();
});

it('renders the contracts page for a fresh user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user)->get('/lawyer/contracts')->assertOk();
});

it('renders the templates page for a fresh user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user)->get('/lawyer/templates')->assertOk();
});

it('renders the knowledge-base page for a fresh user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user)->get('/lawyer/knowledge-base')->assertOk();
});

it('renders the law-search page for a fresh user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user)->get('/lawyer/law-search')->assertOk();
});

it('renders the usage page for a fresh user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user)->get('/lawyer/usage')->assertOk();
});

it('renders the audit page for a fresh user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user)->get('/lawyer/audit')->assertOk();
});

it('renders the contracts page with one contract present', function () {
    // A subtle regression has been previously caused by switching $guarded → $fillable
    // on Contract. Keep this case so the contracts index doesn't silently render empty.
    $user = User::factory()->create(['email_verified_at' => now()]);
    Contract::create([
        'user_id' => $user->id,
        'title' => 'NDA between Foo and Bar',
        'body' => 'Article 1 — Parties...',
        'status' => 'draft',
        'version' => 1,
    ]);

    $r = $this->actingAs($user)->get('/lawyer/contracts');
    $r->assertOk();
    $r->assertSee('NDA between Foo and Bar');
});

/* ─────────────────  Settings  ───────────────── */

it('renders settings pages for a verified user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user)->get('/settings/profile')->assertOk();
    $this->actingAs($user)->get('/settings/billing')->assertOk();
    $this->actingAs($user)->get('/settings/appearance')->assertOk();
});

/* ─────────────────  Localisation  ───────────────── */

it('switches locale to Arabic via POST', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user)
        ->from('/dashboard')
        ->post('/locale', ['locale' => 'ar'])
        ->assertRedirect('/dashboard');

    expect($user->fresh()->locale)->toBe('ar');
});

it('rejects unknown locale values, defaulting to en', function () {
    $user = User::factory()->create(['email_verified_at' => now(), 'locale' => 'en']);
    $this->actingAs($user)
        ->from('/dashboard')
        ->post('/locale', ['locale' => 'klingon']);

    expect($user->fresh()->locale)->toBe('en');
});

/* ─────────────────  Health  ───────────────── */

it('exposes the framework /up health endpoint', function () {
    $this->get('/up')->assertOk();
});

it('rejects the internal health endpoint without the key', function () {
    $this->get('/__internal/health')->assertForbidden();
});
