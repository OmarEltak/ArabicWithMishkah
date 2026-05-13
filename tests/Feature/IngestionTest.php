<?php

declare(strict_types=1);

use App\Models\LegalChunk;
use App\Models\LegalDocument;
use App\Services\Ingestion\IngestionService;

it('ingests pasted text and produces chunks with mock embeddings', function () {
    $service = IngestionService::fromConfig();
    $doc = $service->ingestText(
        user: null,
        title: 'Test Statute',
        content: str_repeat('Section 1. The following provisions apply. ', 200),
    );

    expect($doc)->toBeInstanceOf(LegalDocument::class);
    expect($doc->chunk_count)->toBeGreaterThan(0);
    expect(LegalChunk::where('legal_document_id', $doc->id)->count())->toBe($doc->chunk_count);

    $chunk = LegalChunk::where('legal_document_id', $doc->id)->first();
    expect($chunk->embedding)->toBeArray()->and(count($chunk->embedding))->toBe(256);
});

it('rejects too-short documents', function () {
    $service = IngestionService::fromConfig();
    expect(fn () => $service->ingestText(user: null, title: 'Tiny', content: 'too short'))
        ->toThrow(RuntimeException::class);
});
