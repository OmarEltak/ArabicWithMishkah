<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds body_history JSON column. Each save snapshots the previous body
     * here so a lawyer can compare v1 → v2 → vN. Capped at 10 most-recent
     * versions to keep the column from ballooning.
     *
     * Shape: [{version:int, body:string, saved_at:iso8601, saved_by:string}, ...]
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            $table->json('body_history')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            $table->dropColumn('body_history');
        });
    }
};
