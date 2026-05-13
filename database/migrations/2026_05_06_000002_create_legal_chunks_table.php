<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_chunks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('legal_document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->text('content');
            // Embedding stored as JSON-encoded float vector. Cosine computed in PHP.
            $table->json('embedding')->nullable();
            $table->unsignedInteger('embedding_dim')->nullable();
            $table->string('embedding_model')->nullable();
            $table->timestamps();

            $table->index(['legal_document_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_chunks');
    }
};
