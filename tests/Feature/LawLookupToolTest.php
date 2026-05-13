<?php

declare(strict_types=1);

use App\Services\AI\EmbeddingService;
use App\Services\AI\LawLookupTool;
use App\Services\AI\RagService;
use App\Services\Ingestion\EastlawsClient;
use App\Services\Ingestion\EastlawsIngestService;
use App\Services\Ingestion\IngestionService;
use App\Services\Ingestion\TextExtractor;

it('exposes a valid Anthropic tool definition', function () {
    $def = LawLookupTool::definition();
    expect($def['name'])->toBe('lookup_law');
    expect($def['input_schema']['properties'])->toHaveKey('query');
    expect($def['input_schema']['required'])->toContain('query');
});

it('returns a no-results message when KB is empty and eastlaws is disabled', function () {
    $rag = new RagService(new EmbeddingService(provider: 'mock'));
    $eastlaws = new EastlawsIngestService(
        client: new EastlawsClient('https://x', null, null, 0),
        ingestion: new IngestionService($rag, new TextExtractor),
        enabled: false,
    );
    $tool = new LawLookupTool($rag, $eastlaws);

    $out = $tool->execute(['query' => 'NDA confidentiality clause']);
    expect($out)->toContain('NOT_FOUND');
});

it('returns local KB matches without invoking eastlaws when scores are strong', function () {
    // Prime the KB with a document that should match the query in mock-embed space.
    $svc = IngestionService::fromConfig();
    $svc->ingestText(
        user: null,
        title: 'Confidentiality clauses primer',
        content: str_repeat('A confidentiality clause must define confidential information clearly. ', 30),
    );

    $rag = RagService::fromConfig();
    $eastlaws = new EastlawsIngestService(
        client: new EastlawsClient('https://x', null, null, 0),
        ingestion: new IngestionService($rag, new TextExtractor),
        enabled: false,
    );
    $tool = new LawLookupTool($rag, $eastlaws);

    // The query EXACTLY matches one of the chunked sentences, so mock embeddings
    // (deterministic SHA-256 derived) should return very high cosine for it.
    $out = $tool->execute([
        'query' => 'A confidentiality clause must define confidential information clearly.',
    ]);

    expect($out)->toContain('Confidentiality clauses primer');
});
