<?php

declare(strict_types=1);

use App\Models\LegalDocument;
use App\Services\Ingestion\EastlawsClient;
use App\Services\Ingestion\EastlawsIngestService;
use App\Services\Ingestion\FreshnessService;

/**
 * Live integration test against real eastlaws.com. Skipped by default — runs
 * only when EASTLAWS_INTEGRATION_TESTS=1 is set in the environment AND real
 * credentials are present in the .env. This keeps CI green for everyone else
 * while letting the operator verify the live wiring after credential rotation.
 *
 * Run locally with:
 *   EASTLAWS_INTEGRATION_TESTS=1 vendor/bin/pest --filter=EastlawsIntegration
 */
beforeEach(function () {
    if ((string) env('EASTLAWS_INTEGRATION_TESTS', '0') !== '1') {
        $this->markTestSkipped('EASTLAWS_INTEGRATION_TESTS!=1 — opt-in only.');
    }
    if (! config('services.eastlaws.username') || ! config('services.eastlaws.password')) {
        $this->markTestSkipped('eastlaws credentials missing in .env.');
    }
});

it('can authenticate and list countries', function () {
    $client = EastlawsClient::fromConfig();
    expect($client->isConfigured())->toBeTrue();

    $countries = $client->listCountries();
    expect($countries)->toBeArray();
    expect(count($countries))->toBeGreaterThan(0);
});

it('runs a full search → ingest → verify cycle for one document', function () {
    $svc = EastlawsIngestService::fromConfig();
    expect($svc->isEnabled())->toBeTrue();

    // Tiny ingest: 1 page, 1 doc max — keeps the live test cheap.
    $stats = $svc->bulkIngestQuery(null, 'قانون', 1, 1, 1);

    expect($stats['ingested'] + $stats['skipped'])->toBeGreaterThan(0);
    expect($stats['errors'])->toBe(0);

    if (count($stats['documents']) > 0) {
        $docId = $stats['documents'][0]['id'];
        $doc = LegalDocument::find($docId);
        expect($doc)->not->toBeNull();

        // Round-trip via FreshnessService — should be 'unchanged' since we
        // just fetched seconds ago.
        $outcome = FreshnessService::fromConfig()->verify($doc);
        expect(in_array($outcome, ['unchanged', 'updated', 'skipped'], true))->toBeTrue();
    }
});
