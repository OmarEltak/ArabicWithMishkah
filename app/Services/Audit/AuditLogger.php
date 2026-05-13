<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * Append-only audit trail with HMAC chain integrity.
 *
 * Every write reads the previous row's content_hmac, builds a canonical
 * payload that includes that prior hash, signs the payload with HMAC-SHA256,
 * and stores both. Tampering with any row in place breaks the chain
 * permanently — `audit:verify` will surface the exact divergence point.
 *
 * Callers reference an action (verb) and an optional subject (the model it
 * happened TO). Free-form metadata captures whatever's diagnostic at the time
 * (which doc version, which contract id, the diff, the AI cost, etc.).
 *
 * Failure mode: writes that fail are logged to the standard log channel so
 * they're visible without bringing down the requesting user's flow. Callers
 * should NEVER wrap audit calls in try/catch — let exceptions bubble through
 * the framework's error handler and get logged centrally.
 *
 * Concurrency: the chain-index allocation runs inside a transaction with a
 * row-level lock on the previous row so two concurrent writes can't claim
 * the same chain_index. Cost is one extra SELECT-FOR-UPDATE per write.
 */
class AuditLogger
{
    public function log(
        string $action,
        ?Model $subject = null,
        ?string $summary = null,
        array $metadata = [],
        ?int $userId = null,
    ): AuditLog {
        try {
            return DB::transaction(function () use ($action, $subject, $summary, $metadata, $userId) {
                // Fetch the previous chain head with a row lock so concurrent
                // writers don't both claim the same chain_index. We only
                // consider rows that are part of the chain (chain_index NOT
                // NULL) — legacy unsigned rows are outside the integrity
                // chain and cannot be used as a previous head.
                $prev = AuditLog::query()
                    ->whereNotNull('chain_index')
                    ->orderByDesc('chain_index')
                    ->lockForUpdate()
                    ->first();

                $chainIndex = $prev ? ((int) $prev->chain_index + 1) : 0;
                $prevHash = $prev?->content_hmac ?? str_repeat('0', 64);

                $now = now();

                $row = [
                    'chain_index' => $chainIndex,
                    'prev_hash' => $prevHash,
                    'user_id' => $userId ?? Auth::id(),
                    'subject_type' => $subject ? $subject::class : null,
                    'subject_id' => $subject?->getKey(),
                    'action' => $action,
                    'summary' => $summary,
                    'metadata' => $metadata,
                    'ip' => Request::ip(),
                    'user_agent' => substr((string) Request::userAgent(), 0, 255),
                    'created_at' => $now->toDateTimeString(),
                ];

                $row['content_hmac'] = HashChain::signRow($row);

                $audit = AuditLog::create([
                    ...$row,
                    'updated_at' => $now,
                ]);

                return $audit;
            });
        } catch (\Throwable $e) {
            // Audit failures must never break the user-facing flow. Log it.
            Log::error('AuditLogger write failed', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            // Return an unsaved instance so callers can still use ->id-style
            // attributes without null checks (id will be null).
            return new AuditLog;
        }
    }
}
