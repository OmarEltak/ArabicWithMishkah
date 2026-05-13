<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nightly freshness sweep — picks up to 50 stale eastlaws documents and
// verifies them against upstream. Throttled (~3s/request) so a 50-doc batch
// takes ~3 minutes wall-clock. Tune --limit upward as the corpus grows.
Schedule::command('eastlaws:refresh-stale --limit=50 --queue')
    ->dailyAt('03:00')
    ->onOneServer()
    ->withoutOverlapping();

// Nightly database snapshot — keeps the last 14 backups in storage/app/backups.
// Runs at 02:30 (before the freshness sweep) so a snapshot exists from the
// pre-sweep state if anything goes wrong overnight. Two weeks of retention
// matches the maximum incident-response window we'd realistically restore
// from; older backups should be archived off-box (S3) if needed long-term.
Schedule::command('backup --keep=14')
    ->dailyAt('02:30')
    ->onOneServer()
    ->withoutOverlapping();

// Weekly audit-chain integrity check. Walks the entire HMAC chain and
// alerts (via the `errors` log channel) on divergence. Running it as a
// scheduled job — not just on-demand — turns silent tampering into a
// timely alarm. The command exits non-zero on divergence which surfaces
// in the scheduler log.
Schedule::command('audit:verify --json')
    ->weeklyOn(1, '04:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->emailOutputOnFailure(config('lawyer.support_email', 'support@my-lawyer.app'));

// Reset usage counters at the top of each month so plan caps roll over
// cleanly. PlanUsageGate lazily resets per user, but a top-of-month sweep
// keeps the dashboard aggregates consistent and surfaces stuck counters.
// (Currently a no-op tombstone — the per-user lazy reset is sufficient,
// kept here so future periodic-billing logic has a slot to hook into.)
