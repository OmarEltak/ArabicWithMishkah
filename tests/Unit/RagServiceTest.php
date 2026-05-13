<?php

declare(strict_types=1);

use App\Services\AI\EmbeddingService;
use App\Services\AI\RagService;

it('chunks short text as a single chunk', function () {
    $rag = new RagService(new EmbeddingService(provider: 'mock'), chunkSize: 900, chunkOverlap: 120);
    $chunks = $rag->chunkText('Short legal text.');

    expect($chunks)->toHaveCount(1)->and($chunks[0])->toBe('Short legal text.');
});

it('chunks long text into overlapping pieces', function () {
    $rag = new RagService(new EmbeddingService(provider: 'mock'), chunkSize: 200, chunkOverlap: 40);
    $sentence = 'This is a long legal clause. ';
    $text = str_repeat($sentence, 100); // ~2900 chars
    $chunks = $rag->chunkText($text);

    expect(count($chunks))->toBeGreaterThan(5);
    foreach ($chunks as $chunk) {
        expect(mb_strlen($chunk))->toBeLessThanOrEqual(220);
    }
});

it('computes cosine similarity correctly', function () {
    expect(RagService::cosine([1, 0, 0], [1, 0, 0]))->toEqualWithDelta(1.0, 0.0001);
    expect(RagService::cosine([1, 0, 0], [0, 1, 0]))->toEqualWithDelta(0.0, 0.0001);
    expect(RagService::cosine([1, 1, 0], [1, 0, 0]))->toEqualWithDelta(0.7071, 0.001);
    expect(RagService::cosine([0, 0, 0], [1, 0, 0]))->toEqualWithDelta(0.0, 0.0001);
});

it('produces normalized embeddings from mock provider', function () {
    $svc = new EmbeddingService(provider: 'mock');
    $vec = $svc->embedOne('hello world');
    expect(count($vec))->toBe(256);

    $sum = 0.0;
    foreach ($vec as $x) {
        $sum += $x * $x;
    }
    expect(sqrt($sum))->toEqualWithDelta(1.0, 0.001);
});

it('produces deterministic mock embeddings for the same text', function () {
    $svc = new EmbeddingService(provider: 'mock');
    $a = $svc->embedOne('the quick brown fox');
    $b = $svc->embedOne('the quick brown fox');
    expect($a)->toBe($b);
});
