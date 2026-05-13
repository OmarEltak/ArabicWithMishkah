<?php

declare(strict_types=1);

use App\Services\Contracts\ContractDraftingService;

it('parses JSON wrapped in markdown fences', function () {
    $raw = "```json\n{\"questions\":[\"a\",\"b\"],\"ready_to_draft\":false}\n```";
    $parsed = ContractDraftingService::parseJson($raw);
    expect($parsed['questions'])->toBe(['a', 'b']);
    expect($parsed['ready_to_draft'])->toBeFalse();
});

it('parses JSON with surrounding prose', function () {
    $raw = 'Here is my answer: {"questions":["x"],"ready_to_draft":true} -- end.';
    $parsed = ContractDraftingService::parseJson($raw);
    expect($parsed['ready_to_draft'])->toBeTrue();
});

it('returns empty array on garbage input', function () {
    expect(ContractDraftingService::parseJson('no json here'))->toBe([]);
});

it('extracts unique sorted citation numbers', function () {
    $body = 'See [3], also [1], and again [3] then [12].';
    expect(ContractDraftingService::extractCitations($body))->toBe([1, 3, 12]);
});
