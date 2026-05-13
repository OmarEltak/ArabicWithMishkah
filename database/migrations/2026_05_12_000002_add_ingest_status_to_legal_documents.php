<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the ingest_status column that drives the Search Laws feature.
 *
 *   stub      — we know the doc exists upstream (from a search-list call)
 *               but have not yet fetched its body. `content` is empty.
 *   queued    — body fetch has been queued, not yet running.
 *   indexing  — body fetched; embeddings/chunking in progress.
 *   complete  — full content + chunks + embeddings present.
 *   failed    — last fetch/index attempt errored; eligible for retry.
 *
 * All pre-existing rows are backfilled to 'complete' so RAG retrieval and
 * the freshness scheduler keep behaving identically for the existing corpus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_documents', function (Blueprint $table): void {
            $table->string('ingest_status', 16)->default('complete')->after('chunk_count');
            $table->index('ingest_status');
        });

        DB::table('legal_documents')->update(['ingest_status' => 'complete']);
    }

    public function down(): void
    {
        Schema::table('legal_documents', function (Blueprint $table): void {
            $table->dropIndex(['ingest_status']);
            $table->dropColumn('ingest_status');
        });
    }
};
