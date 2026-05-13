<?php

declare(strict_types=1);

namespace App\Services\Ingestion;

use App\Models\LegalChunk;
use App\Models\LegalDocument;
use App\Services\AI\RagService;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Detects content drift on already-ingested eastlaws documents and refreshes
 * them in place when the upstream changes.
 *
 * Flow per document:
 *   1. Fetch the current full text from eastlaws (throttled).
 *   2. Compute sha256 of normalised content; compare to stored content_hash.
 *   3. If unchanged → bump last_verified_at + next_check_at (no DB churn).
 *   4. If changed → mark every existing chunk superseded_at = now,
 *      bump version on the parent doc, store new content + new hash, and
 *      re-index (which inserts a fresh set of chunks at the new version).
 *
 * The two key invariants:
 *   - We NEVER overwrite or delete prior chunks. Citation history matters
 *     legally — a contract drafted last week needs to remain reproducible
 *     even after the underlying law is amended.
 *   - We ALWAYS update last_verified_at when a verification call succeeds,
 *     even when nothing changed. The UI surfaces this so users can see the
 *     freshness of every cited authority.
 */
class FreshnessService
{
    public function __construct(
        private readonly EastlawsClient $client,
        private readonly RagService $rag,
        private readonly bool $eastlawsEnabled,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            client: EastlawsClient::fromConfig(),
            rag: RagService::fromConfig(),
            eastlawsEnabled: (bool) config('services.eastlaws.enabled', false)
                && (bool) config('services.eastlaws.username')
                && (bool) config('services.eastlaws.password'),
        );
    }

    public function isEnabled(): bool
    {
        return $this->eastlawsEnabled && $this->client->isConfigured();
    }

    /**
     * Compute the canonical content hash for a piece of text. Whitespace
     * collapsing keeps the hash stable across cosmetic upstream changes.
     */
    public static function hashContent(string $text): string
    {
        $normalised = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);

        return hash('sha256', $normalised);
    }

    /**
     * Verify a single document. Returns one of:
     *   - 'unchanged' : remote matches stored hash
     *   - 'updated'   : remote differed; we superseded old chunks + re-indexed
     *   - 'skipped'   : non-eastlaws doc or eastlaws disabled
     *   - 'failed'    : network/parse error (logged; next_check_at NOT advanced)
     */
    public function verify(LegalDocument $doc): string
    {
        if ($doc->source !== 'eastlaws' || ! $this->isEnabled()) {
            return 'skipped';
        }

        [$recType, $recId] = $this->parseSourceRef($doc->source_ref);
        if ($recId === null || $recType === null) {
            Log::channel('ai')->warning('Freshness: malformed source_ref, cannot verify', [
                'doc_id' => $doc->id,
                'source_ref' => $doc->source_ref,
            ]);

            return 'failed';
        }

        try {
            $html = $this->client->fetchFullText($recId, $recType);
            $text = EastlawsClient::htmlToCleanText($html);
        } catch (\Throwable $e) {
            Log::channel('ai')->warning('Freshness: eastlaws fetch failed', [
                'doc_id' => $doc->id,
                'error' => $e->getMessage(),
            ]);

            return 'failed';
        }

        if (mb_strlen($text) < 80) {
            Log::channel('ai')->warning('Freshness: eastlaws returned empty text on refetch', ['doc_id' => $doc->id]);

            return 'failed';
        }

        $remoteHash = self::hashContent($text);
        $storedHash = $doc->content_hash ?: self::hashContent($doc->content);

        $now = now();

        if ($remoteHash === $storedHash) {
            // Backfill content_hash on first verification of a legacy row.
            $doc->forceFill([
                'content_hash' => $remoteHash,
                'last_verified_at' => $now,
                'next_check_at' => RefreshPolicy::nextCheckAt($doc->refresh_policy ?: RefreshPolicy::STANDARD, $now),
            ])->save();

            app(AuditLogger::class)->log(
                action: 'document.verified',
                subject: $doc,
                summary: 'Verified — no changes detected',
                metadata: ['version' => $doc->version, 'hash' => $remoteHash],
            );

            return 'unchanged';
        }

        // Content changed → version bump + supersede old chunks.
        $newTitle = EastlawsClient::extractTitleFromFullText($html) ?? $doc->title;

        DB::transaction(function () use ($doc, $text, $remoteHash, $newTitle, $now): void {
            // Mark all currently-active chunks as superseded; preserve the rest.
            LegalChunk::query()
                ->where('legal_document_id', $doc->id)
                ->whereNull('superseded_at')
                ->update(['superseded_at' => $now]);

            $newVersion = ((int) ($doc->version ?? 1)) + 1;

            $existingMeta = is_array($doc->metadata) ? $doc->metadata : [];
            $revisions = $existingMeta['revisions'] ?? [];
            $revisions[] = [
                'version' => $newVersion,
                'detected_at' => $now->toIso8601String(),
                'previous_hash' => $doc->content_hash,
            ];

            $doc->forceFill([
                'title' => $newTitle,
                'content' => $text,
                'content_hash' => $remoteHash,
                'version' => $newVersion,
                'last_verified_at' => $now,
                'next_check_at' => RefreshPolicy::nextCheckAt($doc->refresh_policy ?: RefreshPolicy::STANDARD, $now),
                'metadata' => array_merge($existingMeta, ['revisions' => $revisions]),
            ])->save();
        });

        // Re-index AFTER commit so embedding API failures don't roll back the
        // supersession itself. New chunks will land at $newVersion thanks to
        // RagService::indexDocument reading the model's current version.
        try {
            $this->rag->indexDocument($doc->fresh());
        } catch (\Throwable $e) {
            Log::channel('ai')->error('Freshness: re-index failed after supersession', [
                'doc_id' => $doc->id,
                'error' => $e->getMessage(),
            ]);

            // Old chunks are already superseded; without re-index the doc has
            // no active chunks. Return 'failed' so the scheduler retries —
            // next run will refetch and try indexing again.
            return 'failed';
        }

        app(AuditLogger::class)->log(
            action: 'document.refreshed',
            subject: $doc->fresh(),
            summary: 'Upstream change detected — superseded prior version',
            metadata: [
                'previous_hash' => $storedHash,
                'new_hash' => $remoteHash,
                'new_version' => $doc->fresh()?->version,
            ],
        );

        return 'updated';
    }

    /**
     * Force the next check to be immediate. Used by the kill-switch command
     * when an admin knows a doc has changed before scheduled detection runs.
     */
    public function invalidate(LegalDocument $doc): void
    {
        $doc->forceFill([
            'next_check_at' => now(),
        ])->save();

        app(AuditLogger::class)->log(
            action: 'document.invalidated',
            subject: $doc,
            summary: 'Manually invalidated — forced recheck',
        );
    }

    /**
     * @return array{0: int|null, 1: int|null}  [recType, recId]
     */
    private function parseSourceRef(?string $sourceRef): array
    {
        if (! is_string($sourceRef) || ! str_contains($sourceRef, ':')) {
            return [null, null];
        }
        [$recType, $recId] = array_pad(explode(':', $sourceRef, 2), 2, null);
        if (! is_numeric($recType) || ! is_numeric($recId)) {
            return [null, null];
        }

        return [(int) $recType, (int) $recId];
    }
}
