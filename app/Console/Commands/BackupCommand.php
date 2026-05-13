<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Snapshot the SQLite database file (and optionally a copy of .env) into a
 * timestamped backup folder. Cheap insurance for the demo phase before a
 * proper Postgres + WAL-archive setup is wired up.
 *
 *   php artisan backup
 *   php artisan backup --keep=14    # keep last 14 backups, prune older
 *
 * On Windows we copy the file in-place; SQLite WAL-mode means the on-disk
 * file is consistent at any moment as long as no write transaction is
 * mid-flight. For a busy production load we'd use the dedicated
 * `sqlite3 .backup` command, but for a single-tenant MVP this is enough.
 */
class BackupCommand extends Command
{
    protected $signature = 'backup
        {--keep=7 : Number of timestamped backups to retain (older ones pruned).}
        {--include-env : Also copy a snapshot of .env (no secrets are scrubbed — handle the artifact carefully).}';

    protected $description = 'Snapshot the SQLite database into storage/backups/.';

    public function handle(): int
    {
        $dbPath = (string) config('database.connections.'.config('database.default').'.database');
        if (! is_file($dbPath)) {
            $this->error('Database file not found at: '.$dbPath);

            return self::FAILURE;
        }

        $stamp = now()->format('Ymd-His');
        $dir = storage_path('app/backups/'.$stamp);
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            $this->error('Could not create backup dir: '.$dir);

            return self::FAILURE;
        }

        $dbBackup = $dir.'/database.sqlite';
        if (! copy($dbPath, $dbBackup)) {
            $this->error('Database copy failed.');

            return self::FAILURE;
        }
        $bytes = filesize($dbBackup);
        $this->info(sprintf('Database backed up: %s (%.1f MB)', $dbBackup, $bytes / 1024 / 1024));

        if ($this->option('include-env') && is_file(base_path('.env'))) {
            copy(base_path('.env'), $dir.'/.env.snapshot');
            $this->warn('  ⚠ .env snapshot included — handle this folder as secret material.');
        }

        Log::info('Backup snapshot created', ['path' => $dir, 'bytes' => $bytes]);

        // Prune old backups
        $keep = max(1, (int) $this->option('keep'));
        $root = storage_path('app/backups');
        $entries = is_dir($root)
            ? array_values(array_filter(scandir($root) ?: [], fn ($n) => $n !== '.' && $n !== '..' && is_dir($root.'/'.$n)))
            : [];
        sort($entries);
        $toPrune = array_slice($entries, 0, max(0, count($entries) - $keep));
        foreach ($toPrune as $old) {
            self::rrmdir($root.'/'.$old);
            $this->line('  pruned: '.$old);
        }

        return self::SUCCESS;
    }

    private static function rrmdir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) ?: [] as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            $path = $dir.'/'.$f;
            is_dir($path) ? self::rrmdir($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
