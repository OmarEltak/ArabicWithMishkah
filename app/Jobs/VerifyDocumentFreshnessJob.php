<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\LegalDocument;
use App\Services\Ingestion\FreshnessService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Background freshness check for a single LegalDocument. Spawned in batches by
 * the eastlaws:refresh-stale scheduled command, or manually via the "Refresh"
 * button in the Knowledge Base UI.
 *
 * Exits as a no-op if the document is missing, non-eastlaws, or eastlaws is
 * disabled — the FreshnessService handles those cases.
 */
class VerifyDocumentFreshnessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 2;

    public int $backoff = 60;

    public function __construct(public readonly int $documentId) {}

    public function handle(FreshnessService $freshness): void
    {
        $doc = LegalDocument::query()->find($this->documentId);
        if ($doc === null) {
            return;
        }

        $outcome = $freshness->verify($doc);

        Log::channel('ai')->info('Freshness check complete', [
            'doc_id' => $doc->id,
            'outcome' => $outcome,
            'version' => $doc->fresh()?->version,
        ]);
    }
}
