<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_chunks', function (Blueprint $table): void {
            // Bumped each time the parent document is re-ingested with new
            // content. Chunks from prior versions remain readable but are
            // filtered out of RAG search via superseded_at.
            $table->unsignedInteger('version')->default(1)->after('embedding_model');

            // Set when a newer version of the parent document is ingested.
            // RAG search ignores rows where this is non-null.
            $table->timestamp('superseded_at')->nullable()->after('version');

            $table->index(['legal_document_id', 'superseded_at']);
        });
    }

    public function down(): void
    {
        Schema::table('legal_chunks', function (Blueprint $table): void {
            $table->dropIndex(['legal_document_id', 'superseded_at']);
            $table->dropColumn(['version', 'superseded_at']);
        });
    }
};
