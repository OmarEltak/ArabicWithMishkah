<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cache layer for user-driven law search.
 *
 *   normalized_query       — Arabic-normalised, lower-cased version of the
 *                            user input; the unique lookup key together with
 *                            jurisdiction.
 *   raw_query              — last raw query string that hit this row, for
 *                            debugging / analytics.
 *   jurisdiction           — ISO-2 code or NULL for cross-jurisdiction.
 *   upstream_result_count  — number of refs the upstream search-list returned.
 *   ingested_count         — number of those refs that are now ingest_status=complete.
 *   search_count           — total times users have run this query (drives the
 *                            popular-queries scheduled bulk-ingest).
 *   last_searched_at       — wall clock of the last upstream search-list call.
 *                            If within the cache TTL, the upstream call is skipped.
 *   status                 — pending | searched | exhausted | rate_limited
 *
 * Without this table, every Search button press re-hits eastlaws for rare
 * terms — that's both a ToS risk and a wasted API budget.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_search_queries', function (Blueprint $table): void {
            $table->id();
            $table->string('normalized_query', 255);
            $table->string('raw_query', 255);
            $table->string('jurisdiction', 8)->nullable();
            $table->unsignedInteger('upstream_result_count')->default(0);
            $table->unsignedInteger('ingested_count')->default(0);
            $table->unsignedInteger('search_count')->default(1);
            $table->timestamp('last_searched_at')->nullable();
            $table->string('status', 16)->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['normalized_query', 'jurisdiction'], 'lsq_normalized_jurisdiction_unique');
            $table->index('last_searched_at');
            $table->index('search_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_search_queries');
    }
};
