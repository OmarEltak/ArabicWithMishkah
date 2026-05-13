<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a native pgvector column to legal_chunks for SQL-side cosine search.
 *
 * No-op on every driver except Postgres. On Postgres, attempts to enable the
 * pgvector extension and add an `embedding_pgv vector` column. The column is
 * UNTYPED (no fixed dimension) so it works with both the 256-d mock and the
 * 1024-d Voyage embeddings — RagService validates dimensions at write time.
 *
 * Existing rows are NOT backfilled here. Run:
 *
 *     php artisan rag:promote-to-pgvector
 *
 * after this migration to populate embedding_pgv from the JSON `embedding`
 * column for any documents already indexed under SQLite/JSON.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        // Best-effort extension install. Requires a superuser-equivalent role
        // on first run; harmless thereafter.
        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        } catch (\Throwable $e) {
            // Extension not available on this Postgres install. The pgvector
            // path will simply stay disabled; in-PHP cosine continues to work.
            return;
        }

        // Add the column only if the extension actually registered the type.
        $hasVectorType = (bool) DB::scalar("SELECT 1 FROM pg_type WHERE typname = 'vector'");
        if (! $hasVectorType) {
            return;
        }

        if (! Schema::hasColumn('legal_chunks', 'embedding_pgv')) {
            DB::statement('ALTER TABLE legal_chunks ADD COLUMN embedding_pgv vector');
        }

        // HNSW index is the right choice for cosine; falls back to no-index
        // if the build version doesn't support hnsw (pgvector < 0.5.0).
        try {
            DB::statement(
                'CREATE INDEX IF NOT EXISTS legal_chunks_embedding_pgv_hnsw
                 ON legal_chunks USING hnsw (embedding_pgv vector_cosine_ops)'
            );
        } catch (\Throwable $e) {
            // older pgvector — try ivfflat, then give up silently.
            try {
                DB::statement(
                    'CREATE INDEX IF NOT EXISTS legal_chunks_embedding_pgv_ivf
                     ON legal_chunks USING ivfflat (embedding_pgv vector_cosine_ops) WITH (lists = 100)'
                );
            } catch (\Throwable) {
                // No vector index. Sequential scan is fine up to ~50k rows.
            }
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }
        try {
            DB::statement('DROP INDEX IF EXISTS legal_chunks_embedding_pgv_hnsw');
            DB::statement('DROP INDEX IF EXISTS legal_chunks_embedding_pgv_ivf');
            DB::statement('ALTER TABLE legal_chunks DROP COLUMN IF EXISTS embedding_pgv');
        } catch (\Throwable) {
            // safe rollback
        }
    }
};
