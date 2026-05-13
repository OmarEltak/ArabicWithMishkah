<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Reports\HealthReport;
use Illuminate\Console\Command;

/**
 * CLI variant of /__internal/health for oncall use.
 *
 * Usage:
 *   php artisan health:report
 *   php artisan health:report --json
 */
class HealthReportCommand extends Command
{
    protected $signature = 'health:report {--json : JSON output for piping}';
    protected $description = 'Print a production-health snapshot (corpus, queue, audit chain, providers)';

    public function handle(HealthReport $report): int
    {
        $r = $report->generate();

        if ($this->option('json')) {
            $this->line(json_encode($r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return self::SUCCESS;
        }

        $this->newLine();
        $this->line('<options=bold>Production health snapshot</> · '.$r['as_of'].' · env='.$r['app_env']);
        $this->newLine();

        $this->section('Corpus', $r['corpus']);
        $this->section('Queue', $r['queue']);
        $this->section('Audit chain', $r['audit_chain']);
        $this->section('Providers', $r['providers']);
        $this->section('Uptime', $r['uptime']);

        return $this->overallStatus($r) === 'healthy' ? self::SUCCESS : self::FAILURE;
    }

    private function section(string $title, mixed $body): void
    {
        $status = is_array($body) ? ($body['status'] ?? null) : null;
        $color = match ($status) {
            'healthy', 'configured' => 'green',
            'degraded', 'has_failures', 'backlogged' => 'yellow',
            'stale', 'tampered', 'error', 'unconfigured' => 'red',
            default => 'gray',
        };
        $statusBadge = $status ? sprintf(' <fg=%s>%s</>', $color, strtoupper($status)) : '';

        $this->line(sprintf('  <options=bold>%s</>%s', $title, $statusBadge));
        if (is_array($body)) {
            foreach ($body as $k => $v) {
                if ($k === 'status') {
                    continue;
                }
                $this->line(sprintf('    %-22s %s', $k, $this->fmtVal($v)));
            }
        }
        $this->newLine();
    }

    private function fmtVal(mixed $v): string
    {
        if (is_bool($v)) {
            return $v ? 'true' : 'false';
        }
        if (is_array($v)) {
            return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        return (string) ($v ?? '∅');
    }

    /**
     * @param array<string, mixed> $r
     */
    private function overallStatus(array $r): string
    {
        $statuses = [
            $r['corpus']['status'] ?? 'unknown',
            $r['queue']['status'] ?? 'unknown',
            $r['audit_chain']['status'] ?? 'unknown',
        ];
        if (in_array('tampered', $statuses, true) || in_array('error', $statuses, true)) {
            return 'critical';
        }
        if (in_array('stale', $statuses, true) || in_array('has_failures', $statuses, true)) {
            return 'degraded';
        }
        return 'healthy';
    }
}
