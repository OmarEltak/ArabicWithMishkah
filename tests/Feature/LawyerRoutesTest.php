<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\ContractTemplateSeeder;
use Database\Seeders\SampleLegalReferenceSeeder;

it('redirects guests away from lawyer routes', function () {
    foreach (['/lawyer/chat', '/lawyer/contracts', '/lawyer/templates', '/lawyer/knowledge-base'] as $path) {
        $this->get($path)->assertRedirect('/login');
    }
});

it('renders all lawyer routes for an authenticated user', function () {
    $this->seed(ContractTemplateSeeder::class);
    $this->seed(SampleLegalReferenceSeeder::class);

    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    // Each assertion targets a stable piece of copy on the redesigned pages.
    $this->get('/dashboard')->assertOk()->assertSee('Operations overview');
    $this->get('/lawyer/knowledge-base')->assertOk()->assertSee('Legal authority library');
    $this->get('/lawyer/chat')->assertOk()->assertSee('Describe what you need to draft');
    $this->get('/lawyer/contracts')->assertOk()->assertSee('Saved contracts');
    $this->get('/lawyer/templates')->assertOk()->assertSee('Mutual Non-Disclosure Agreement');
});
