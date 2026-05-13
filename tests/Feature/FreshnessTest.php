<?php

declare(strict_types=1);

use App\Models\LegalChunk;
use App\Models\LegalDocument;
use App\Services\AI\EmbeddingService;
use App\Services\AI\RagService;
use App\Services\Ingestion\FreshnessService;
use App\Services\Ingestion\IngestionService;
use App\Services\Ingestion\RefreshPolicy;
use App\Services\Ingestion\TextExtractor;

beforeEach(function () {
    // Local-only RAG so tests don't touch the network.
    $this->rag = new RagService(
        embeddings: new EmbeddingService(provider: 'mock'),
    );
    $this->ingest = new IngestionService(
        rag: $this->rag,
        extractor: new TextExtractor,
    );
});

it('classifies eastlaws documents into refresh policies', function () {
    expect(RefreshPolicy::classifyEastlaws(2, 'Cassation Court Ruling 1234/2023'))
        ->toBe(RefreshPolicy::IMMUTABLE);
    expect(RefreshPolicy::classifyEastlaws(1, 'دستور جمهورية مصر العربية'))
        ->toBe(RefreshPolicy::SLOW);
    expect(RefreshPolicy::classifyEastlaws(1, 'قرار وزاري رقم 50'))
        ->toBe(RefreshPolicy::FAST);
    expect(RefreshPolicy::classifyEastlaws(1, 'Egyptian Civil Code'))
        ->toBe(RefreshPolicy::STANDARD);
});

it('returns next-check intervals matching the policy tier', function () {
    expect(RefreshPolicy::intervalDays(RefreshPolicy::IMMUTABLE))->toBeNull();
    expect(RefreshPolicy::intervalDays(RefreshPolicy::FAST))->toBe(7);
    expect(RefreshPolicy::intervalDays(RefreshPolicy::STANDARD))->toBe(30);
    expect(RefreshPolicy::intervalDays(RefreshPolicy::SLOW))->toBe(90);
});

it('hashes content stably across cosmetic whitespace differences', function () {
    $a = "Article 1.\n\nThis is the law.";
    $b = "Article 1.   This is the law.   ";

    expect(FreshnessService::hashContent($a))->toBe(FreshnessService::hashContent($b));
});

it('does NOT schedule a freshness check for non-eastlaws documents', function () {
    $doc = $this->ingest->ingestText(
        user: null,
        title: 'Pasted note',
        content: str_repeat('Some pasted legal note content. ', 10),
        source: 'paste',
    );

    expect($doc->next_check_at)->toBeNull();
    expect($doc->last_verified_at)->toBeNull();
    expect($doc->content_hash)->not->toBeNull();
});

it('marks active chunks superseded when a document is re-indexed', function () {
    $doc = $this->ingest->ingestText(
        user: null,
        title: 'Some statute v1',
        content: str_repeat('Original article text. ', 50),
    );

    $original = LegalChunk::where('legal_document_id', $doc->id)->get();
    expect($original)->not->toBeEmpty();
    expect($original->whereNull('superseded_at')->count())->toBe($original->count());

    // Simulate an update: bump version + replace content + reindex.
    $doc->forceFill([
        'content' => str_repeat('Amended article text. ', 50),
        'version' => 2,
    ])->save();
    $this->rag->indexDocument($doc->fresh());

    $reloaded = LegalChunk::where('legal_document_id', $doc->id)->get();
    $active = $reloaded->whereNull('superseded_at');
    $superseded = $reloaded->whereNotNull('superseded_at');

    expect($superseded->count())->toBe($original->count());
    expect($active->count())->toBeGreaterThan(0);
    expect($active->pluck('version')->unique()->all())->toBe([2]);
});

it('search() ignores superseded chunks', function () {
    $doc = $this->ingest->ingestText(
        user: null,
        title: 'Confidentiality primer',
        content: str_repeat('A confidentiality clause must define confidential information clearly. ', 30),
    );

    // Mark every active chunk superseded.
    LegalChunk::where('legal_document_id', $doc->id)
        ->update(['superseded_at' => now()]);

    $results = $this->rag->search('confidentiality clause must define confidential information', k: 4);

    expect($results)->toBeEmpty();
});

it('next_check_at is populated for eastlaws-sourced ingestion', function () {
    $doc = LegalDocument::create([
        'title' => 'Eastlaws sample',
        'source' => 'eastlaws',
        'source_ref' => '1:9999',
        'language' => 'ar',
        'content' => 'sample',
        'content_hash' => FreshnessService::hashContent('sample'),
        'refresh_policy' => RefreshPolicy::STANDARD,
        'last_verified_at' => now(),
        'next_check_at' => RefreshPolicy::nextCheckAt(RefreshPolicy::STANDARD),
        'version' => 1,
    ]);

    expect($doc->next_check_at)->not->toBeNull();
    expect($doc->isStale())->toBeFalse();
});

it('flags eastlaws docs without next_check_at as stale', function () {
    $doc = LegalDocument::create([
        'title' => 'Legacy eastlaws row',
        'source' => 'eastlaws',
        'source_ref' => '1:1',
        'language' => 'ar',
        'content' => 'sample',
    ]);

    expect($doc->isStale())->toBeTrue();
});
