<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\LegalDocument;
use App\Services\Ingestion\EastlawsIngestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Fills the body + chunks + embeddings for a single upstream document
 * reference. Dispatched per-row by LawSearchUpstreamJob after stub creation,
 * or directly by the search-results UI when the user explicitly clicks
 * "Open" on a stub (priority queue).
 *
 * The EastlawsClient enforces a per-process throttle (~3s/request), so
 * multiple parallel workers do NOT bypass it — the upstream rate limit is
 * respected regardless of queue concurrency.
 */
class IngestEastlawsDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 2;

    public int $backoff = 30;

    public function __construct(
        public readonly int $recId,
        public readonly int $recType,
        public readonly string $slug,
        public readonly int $countryId = 1,
        public readonly ?string $category = null,
    ) {}

    public function handle(EastlawsIngestService $svc): void
    {
        if (! $svc->isEnabled()) {
            Log::channel('ai')->info('IngestEastlawsDocumentJob skipped — service disabled', [
                'rec_id' => $this->recId,
                'rec_type' => $this->recType,
            ]);

            return;
        }

        try {
            $svc->fetchAndIngestOne(
                user: null,
                recId: $this->recId,
                recType: $this->recType,
                slug: $this->slug,
                countryId: $this->countryId,
                category: $this->category,
            );
        } catch (\Throwable $e) {
            Log::channel('ai')->warning('IngestEastlawsDocumentJob failed', [
                'rec_id' => $this->recId,
                'rec_type' => $this->recType,
                'error' => $e->getMessage(),
            ]);

            // Mark any stub as failed so the UI stops spinning on it.
            LegalDocument::query()
                ->where('source', 'eastlaws')
                ->where('source_ref', $this->recType.':'.$this->recId)
                ->update(['ingest_status' => LegalDocument::INGEST_FAILED]);

            throw $e;
        }
    }
}
