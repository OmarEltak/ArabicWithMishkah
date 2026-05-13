<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * legal_clippings — per-user research bookmarks against the indexed corpus.
 *
 * The lawyer searches for "article 148 civil code", reads it, and clicks
 * "Save to clippings". A row here pins that {document, chunk?, note, tags}
 * to their workspace so they can come back to it without re-searching.
 *
 * Why a separate table vs. attaching notes to LegalChunk:
 *   - Clippings are personal (per user); chunks are shared corpus.
 *   - Clippings have lifecycle (note + tags evolve); chunks are immutable.
 *   - Clippings can outlive a chunk that's been re-ingested under a new id
 *     because the document_id is the durable anchor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_clippings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('legal_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('legal_chunk_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();      // optional override of doc title for the lawyer's own labelling
            $table->text('snippet')->nullable();      // the highlighted excerpt at the moment of clipping
            $table->text('note')->nullable();         // freeform user note
            $table->json('tags')->nullable();         // array of strings; queryable for filtering
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'legal_document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_clippings');
    }
};
