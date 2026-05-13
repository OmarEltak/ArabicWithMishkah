<?php

declare(strict_types=1);

use App\Services\Contracts\ContractDraftingService;

it('detects explicit SORRY refusal', function () {
    $body = "SORRY: I cannot draft this because eastlaws has no authority for the following topic(s):\n  - arbitration enforcement";
    expect(ContractDraftingService::isRefusal($body))->toBeTrue();
});

it('detects implicit refusal phrasing', function () {
    $body = 'I checked eastlaws but no authority was found for that specific provision. I cannot draft this clause.';
    expect(ContractDraftingService::isRefusal($body))->toBeTrue();
});

it('does not flag normal drafts as refusals', function () {
    $body = str_repeat('MUTUAL NON-DISCLOSURE AGREEMENT This Agreement is entered into between the parties. ', 20);
    expect(ContractDraftingService::isRefusal($body))->toBeFalse();
});

it('does not flag short non-refusal drafts as refusals', function () {
    $body = 'Article 1: The parties agree to keep all confidential information private. Article 2: Term is 2 years.';
    expect(ContractDraftingService::isRefusal($body))->toBeFalse();
});
