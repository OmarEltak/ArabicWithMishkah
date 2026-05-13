<?php

declare(strict_types=1);

use App\Models\LegalChunk;
use App\Services\AI\CitationVerifier;
use App\Services\AI\EmbeddingService;
use App\Services\Ingestion\IngestionService;

beforeEach(function () {
    // Mock embeddings so the SHA-derived vectors are stable + deterministic.
    $this->embeddings = new EmbeddingService(provider: 'mock');
    $this->verifier = new CitationVerifier($this->embeddings);
});

it('returns empty audit for a draft with no markers', function () {
    $audit = $this->verifier->audit('A simple draft with no citations.', []);

    expect($audit['markers'])->toBe([]);
    expect($audit['summary']['total'])->toBe(0);
});

it('extracts markers and the surrounding sentence', function () {
    $body = 'First sentence here. Second sentence cites authority [1]. Third sentence stands alone.';

    $markers = CitationVerifier::extractMarkers($body);

    expect($markers)->toHaveCount(1);
    $first = array_values($markers)[0];
    expect($first['n'])->toBe(1);
    expect($first['snippet'])->toContain('Second sentence cites authority');
});

it('flags every marker as unverified when no chunks were seen', function () {
    $body = 'This clause relies on [1] and also on [2].';

    $audit = $this->verifier->audit($body, []);

    expect($audit['markers'])->toHaveCount(2);
    foreach ($audit['markers'] as $row) {
        expect($row['verdict'])->toBe('unverified');
        expect($row['score'])->toBe(0.0);
        expect($row['chunk_id'])->toBeNull();
    }
});

it('verifies a marker when its snippet matches a chunk exactly', function () {
    // Mock embeddings are SHA-derived: identical strings → identical vectors.
    // For a meaningful "verified" path test, the extracted snippet has to
    // equal the indexed chunk text. We construct the body so the [1] marker
    // sits inside a sentence whose content is exactly the stored chunk.
    $svc = IngestionService::fromConfig();
    $doc = $svc->ingestText(
        user: null,
        title: 'Confidentiality primer',
        content: 'A confidentiality clause must define confidential information clearly.',
    );

    $chunk = LegalChunk::where('legal_document_id', $doc->id)->first();
    expect($chunk)->not->toBeNull();

    // Snippet extractor walks back to nearest "\n" / ". " then forward to next.
    // With this layout the [1] sits flush against the chunk text, so after
    // stripping the marker and collapsing whitespace the snippet equals the
    // chunk content exactly. Mock embeddings are SHA-derived → identical
    // input gives identical vector → cosine = 1.0.
    $body = $chunk->content."[1]\nNext sentence.";

    $audit = $this->verifier->audit($body, [$chunk->id]);

    expect($audit['markers'])->toHaveCount(1);
    $first = array_values($audit['markers'])[0];
    expect($first['score'])->toBeGreaterThan(CitationVerifier::VERIFIED_THRESHOLD);
    expect($first['verdict'])->toBe('verified');
});

it('summary tally reflects every verdict', function () {
    $body = 'Clause A [1]. Clause B [2]. Clause C [3].';

    $audit = $this->verifier->audit($body, []);  // no chunks → all unverified

    expect($audit['summary'])->toMatchArray([
        'verified' => 0,
        'uncertain' => 0,
        'unverified' => 3,
        'total' => 3,
    ]);
});
