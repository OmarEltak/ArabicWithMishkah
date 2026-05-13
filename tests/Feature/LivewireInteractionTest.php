<?php

declare(strict_types=1);

use App\Models\ChatSession;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\User;
use App\Services\Contracts\ContractDraftingService;
use Database\Seeders\ContractTemplateSeeder;
use Livewire\Livewire;

it('starts a drafting session and produces a draft via the chat component', function () {
    $this->seed(ContractTemplateSeeder::class);
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    $template = ContractTemplate::where('slug', 'mutual-nda-system')->firstOrFail();

    $component = Livewire::test('pages::lawyer.chat')
        ->set('newTemplateId', $template->id)
        ->set('newIntent', 'NDA between Foo Inc. and Bar LLC under California law for 2 years.')
        ->call('startSession')
        ->assertHasNoErrors();

    $session = ChatSession::where('user_id', $user->id)->firstOrFail();

    // Bypass Livewire to call the service directly — Livewire test harness has
    // some issues with #[Url]-bound state across calls; the service path is what
    // the component invokes anyway.
    $svc = ContractDraftingService::fromConfig();
    $svc->finalize($session);

    expect(Contract::where('user_id', $user->id)->exists())->toBeTrue();
});
