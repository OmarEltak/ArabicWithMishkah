<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Services\Audit\HashChain;
use Illuminate\Console\Command;

/**
 * Walks the entire audit-log HMAC chain and reports any divergence.
 *
 * What "diverged" means:
 *   - A row's stored content_hmac doesn't match what we recompute from
 *     its current contents (someone edited the row in place)
 *   - A row's prev_hash doesn't match the previous row's content_hmac
 *     (a row was deleted, inserted out of order, or reindexed)
 *   - chain_index gaps (a row was deleted)
 *
 * The first divergence is fatal — every row after it is suspect because
 * the chain is forward-signed. The command exits with a non-zero status
 * so you can wire this into CI / cron alerting.
 *
 * Usage:
 *   php artisan audit:verify
 *   php artisan audit:verify --from=10000     # start scan at chain_index N
 *   php artisan audit:verify --json
 */
class AuditVerifyCommand extends Command
{
    protected $signature = 'audit:verify
                            {--from=0 : chain_index to start verification from}
                            {--limit= : Max rows to verify (default: all)}
                            {--json : Emit a JSON report instead of human-readable output}';

    protected $description = 'Verify the audit-log HMAC chain integrity';

    public function handle(): int
    {
        $from = max(0, (int) $this->option('from'));
        $limit = $this->option('limit') === null ? null : max(1, (int) $this->option('limit'));
        $isJson = (bool) $this->option('json');

        $query = AuditLog::query()
            ->whereNotNull('chain_index')
            ->where('chain_index', '>=', $from)
            ->orderBy('chain_index');
        if ($limit !== null) {
            $query->limit($limit);
        }

        $expectedIndex = $from;
        $expectedPrev = $from === 0
            ? str_repeat('0', 64)
            : (AuditLog::query()->where('chain_index', $from - 1)->value('content_hmac') ?? str_repeat('0', 64));

        $verified = 0;
        $divergence = null;

        $unsignedCount = AuditLog::query()->whereNull('content_hmac')->count();

        $query->cursor()->each(function (AuditLog $row) use (&$expectedIndex, &$expectedPrev, &$verified, &$divergence) {
            if ($divergence !== null) {
                return false;
            }
            if ((int) $row->chain_index !== $expectedIndex) {
                $divergence = [
                    'kind' => 'chain_index_gap',
                    'at_id' => $row->id,
                    'expected_index' => $expectedIndex,
                    'found_index' => (int) $row->chain_index,
                ];

                return false;
            }
            if (! hash_equals($expectedPrev, (string) $row->prev_hash)) {
                $divergence = [
                    'kind' => 'prev_hash_mismatch',
                    'at_id' => $row->id,
                    'chain_index' => (int) $row->chain_index,
                    'expected_prev' => $expectedPrev,
                    'found_prev' => (string) $row->prev_hash,
                ];

                return false;
            }

            $rowArr = [
                'chain_index' => (int) $row->chain_index,
                'prev_hash' => (string) $row->prev_hash,
                'user_id' => $row->user_id,
                'subject_type' => $row->subject_type,
                'subject_id' => $row->subject_id,
                'action' => $row->action,
                'summary' => $row->summary,
                'metadata' => $row->metadata,
                'ip' => $row->ip,
                'user_agent' => $row->user_agent,
                'created_at' => $row->created_at?->toDateTimeString(),
            ];
            $recomputed = HashChain::signRow($rowArr);

            if ($recomputed !== (string) $row->content_hmac) {
                $divergence = [
                    'kind' => 'content_hmac_mismatch',
                    'at_id' => $row->id,
                    'chain_index' => (int) $row->chain_index,
                    'stored' => (string) $row->content_hmac,
                    'recomputed' => $recomputed,
                ];

                return false;
            }

            $verified++;
            $expectedIndex++;
            $expectedPrev = (string) $row->content_hmac;

            return true;
        });

        $payload = [
            'verified' => $verified,
            'unsigned_legacy_rows' => $unsignedCount,
            'divergence' => $divergence,
            'ok' => $divergence === null,
        ];

        if ($isJson) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $divergence === null ? self::SUCCESS : self::FAILURE;
        }

        $this->newLine();
        if ($divergence === null) {
            $this->line(sprintf('  <fg=green;options=bold>✓ Chain valid.</> Verified %d signed rows from chain_index %d.', $verified, $from));
            if ($unsignedCount > 0) {
                $this->line(sprintf('  <fg=yellow>!</> %d legacy rows are unsigned (created before HMAC chain was added). They are not part of the integrity chain.', $unsignedCount));
            }
            $this->newLine();

            return self::SUCCESS;
        }

        $this->line('  <fg=red;options=bold>✗ Chain divergence detected.</>');
        $this->line(sprintf('    Kind:           %s', $divergence['kind']));
        $this->line(sprintf('    Audit row ID:   %s', $divergence['at_id'] ?? '?'));
        $this->line(sprintf('    Chain index:    %s', $divergence['chain_index'] ?? $divergence['expected_index'] ?? '?'));
        if (isset($divergence['stored']) && isset($divergence['recomputed'])) {
            $this->line(sprintf('    Stored hmac:    %s', $divergence['stored']));
            $this->line(sprintf('    Recomputed:     %s', $divergence['recomputed']));
        }
        if (isset($divergence['expected_prev']) && isset($divergence['found_prev'])) {
            $this->line(sprintf('    Expected prev:  %s', $divergence['expected_prev']));
            $this->line(sprintf('    Found prev:     %s', $divergence['found_prev']));
        }
        $this->line(sprintf('    Verified ok up to: %d rows', $verified));
        $this->newLine();
        $this->error('Audit-log integrity FAILED. Investigate the row above and consider rotation.');

        return self::FAILURE;
    }
}
