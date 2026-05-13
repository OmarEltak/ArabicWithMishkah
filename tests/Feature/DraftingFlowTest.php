<?php

declare(strict_types=1);

use App\Models\ChatSession;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\User;
use App\Services\Contracts\ContractDraftingService;
use App\Services\Ingestion\IngestionService;
use Database\Seeders\ContractTemplateSeeder;

it('runs an end-to-end drafting flow with mock LLM and embeddings', function () {
    $this->seed(ContractTemplateSeeder::class);

    $user = User::factory()->create();
    IngestionService::fromConfig()->ingestText(
        user: null,
        title: 'NDA reference clause',
        content: str_repeat('Confidential information must be protected. ', 50),
    );

    $template = ContractTemplate::where('slug', 'mutual-nda-system')->firstOrFail();

    $svc = ContractDraftingService::fromConfig();
    $session = $svc->startSession($user, $template, 'Mutual NDA between Alpha LLC and Beta Inc. governed by Delaware law.');

    expect($session)->toBeInstanceOf(ChatSession::class);

    // First clarifying turn.
    $svc->continueSession($session);

    // Force draft.
    $contract = $svc->finalize($session);
    expect($contract)->toBeInstanceOf(Contract::class);
    expect(strlen($contract->body))->toBeGreaterThan(20);
    expect($contract->user_id)->toBe($user->id);
    expect($contract->status)->toBe('draft');
    expect($contract->session->status)->toBe('drafted');
});
