<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('source')->default('upload'); // upload | url | paste | eastlaws
            $table->string('source_ref')->nullable();    // file path or URL
            $table->string('jurisdiction')->nullable();
            $table->string('language', 8)->default('en');
            $table->json('metadata')->nullable();
            $table->longText('content');
            $table->unsignedInteger('chunk_count')->default(0);
            $table->timestamp('ingested_at')->nullable();
            $table->timestamps();

            $table->index(['source', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
