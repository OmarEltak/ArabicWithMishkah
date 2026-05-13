<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Models\AuditLog;
use App\Models\LegalChunk;
use App\Models\LegalDocument;
use App\Services\Audit\HashChain;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Aggregates production-health signals into one structured report.
 *
 * Consumed by:
 *   - /__internal/health (auth-gated JSON endpoint for uptime monitors)
 *   - oncall CLI (`php artisan health:report`)
 *
 * Cheap to compute (no LLM calls, no embedding work) — designed to be
 * pinged once a minute by a monitoring service.
 */
final class HealthReport
{
    /**
     * @return array<string, mixed>
     */
    public function generate(): array
    {
        return [
            'as_of' => Carbon::now()->toAtomString(),
            'app_env' => app()->environment(),
            'uptime' => $this->uptime(),
            'corpus' => $this->corpus(),
            'queue' => $this->queue(),
            'audit_chain' => $this->auditChain(),
            'providers' => $this->providers(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function uptime(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'memory_used_mb' => round(memory_get_usage(true) / 1024 / 1024, 1),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 1),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function corpus(): array
    {
        $official = (array) config('legal_sources.official_slugs', []);

        $total = LegalDocument::count();
        $totalChunks = LegalChunk::count();
        $officialCount = LegalDocument::whereIn('source', $official)->count();
        $stale = LegalDocument::query()
            ->whereIn('source', $official)
            ->where(fn ($q) => $q->whereNull('next_check_at')->orWhere('next_check_at', '<=', now()))
            ->count();

        $byJurisdiction = LegalDocument::query()
            ->whereNotNull('jurisdiction')
            ->whereIn('source', $official)
            ->selectRaw('jurisdiction, count(*) as n')
            ->groupBy('jurisdiction')
            ->orderBy('jurisdiction')
            ->pluck('n', 'jurisdiction')
            ->toArray();

        $freshPct = $officialCount > 0 ? (int) round(($officialCount - $stale) / $officialCount * 100) : 100;
        $status = $freshPct >= 90 ? 'healthy' : ($freshPct >= 60 ? 'degraded' : 'stale');

        return [
            'status' => $status,
            'documents_total' => $total,
            'chunks_total' => $totalChunks,
            'official_documents' => $officialCount,
            'fresh_official' => max(0, $officialCount - $stale),
            'stale_official' => $stale,
            'fresh_percent' => $freshPct,
            'by_jurisdiction' => $byJurisdiction,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function queue(): array
    {
        // Works for the database queue driver. For Redis/SQS, swap in a
        // driver-specific check in App\Services\Reports\QueueProbe.
        $driver = (string) config('queue.default');
        $payload = ['driver' => $driver];

        try {
            if ($driver === 'database') {
                $pending = DB::table('jobs')->count();
                $failed = DB::table('failed_jobs')->count();
                $oldestPending = DB::table('jobs')->orderBy('id')->value('available_at');

                $payload += [
                    'pending' => $pending,
                    'failed' => $failed,
                    'oldest_pending_at' => $oldestPending ? Carbon::createFromTimestamp($oldestPending)->toAtomString() : null,
                    'status' => $failed === 0 && $pending < 1000 ? 'healthy' : ($failed > 0 ? 'has_failures' : 'backlogged'),
                ];
            } else {
                $payload['status'] = 'unmonitored';
                $payload['note'] = 'Queue probe only implemented for the database driver right now.';
            }
        } catch (\Throwable $e) {
            $payload['status'] = 'error';
            $payload['error'] = $e->getMessage();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function auditChain(): array
    {
        $chained = AuditLog::query()->whereNotNull('chain_index')->count();
        $unsignedLegacy = AuditLog::query()->whereNull('chain_index')->count();
        $head = AuditLog::query()
            ->whereNotNull('chain_index')
            ->orderByDesc('chain_index')
            ->first();

        // Cheap integrity check: just re-sign the head row and compare. A
        // full chain walk is what `audit:verify` is for; this is the
        // fast-path /health probe.
        $headOk = null;
        if ($head) {
            $rowArr = [
                'chain_index' => (int) $head->chain_index,
                'prev_hash' => (string) $head->prev_hash,
                'user_id' => $head->user_id,
                'subject_type' => $head->subject_type,
                'subject_id' => $head->subject_id,
                'action' => $head->action,
                'summary' => $head->summary,
                'metadata' => $head->metadata,
                'ip' => $head->ip,
                'user_agent' => $head->user_agent,
                'created_at' => $head->created_at?->toDateTimeString(),
            ];
            $recomputed = HashChain::signRow($rowArr);
            $headOk = $recomputed === (string) $head->content_hmac;
        }

        return [
            'status' => $headOk === false ? 'tampered' : 'healthy',
            'chained_rows' => $chained,
            'unsigned_legacy_rows' => $unsignedLegacy,
            'head_chain_index' => $head?->chain_index,
            'head_at' => $head?->created_at?->toAtomString(),
            'head_signature_valid' => $headOk,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function providers(): array
    {
        // Read provider chain config; report which keys are present without
        // exposing them. Used by oncall to confirm at least one fallback is
        // configured before a deploy.
        $chain = (array) config('lawyer.providers', []);
        $configured = [];
        foreach ($chain as $name => $cfg) {
            $hasKey = ! empty($cfg['api_key'] ?? null);
            $configured[] = [
                'name' => $name,
                'configured' => $hasKey,
            ];
        }

        return [
            'status' => empty($configured) ? 'unconfigured' : 'configured',
            'count' => count($configured),
            'chain' => $configured,
        ];
    }
}
