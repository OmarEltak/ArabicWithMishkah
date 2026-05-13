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

// Nightly database snapshot — keeps the last 7 backups in storage/app/backups.
// Runs at 02:30 (before the freshness sweep) so a snapshot exists from the
// pre-sweep state if anything goes wrong overnight.
Schedule::command('backup --keep=7')
    ->dailyAt('02:30')
    ->onOneServer()
    ->withoutOverlapping();
