<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\Ingestion\EastlawsIngestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IngestEastlawsQueryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 1;

    public function __construct(
        public readonly ?int $userId,
        public readonly string $query,
        public readonly int $countryId = 1,
        public readonly int $maxDocuments = 10,
        public readonly int $maxPages = 1,
    ) {}

    public function handle(EastlawsIngestService $svc): void
    {
        $user = $this->userId ? User::find($this->userId) : null;
        $svc->bulkIngestQuery($user, $this->query, $this->countryId, $this->maxDocuments, $this->maxPages);
    }
}
