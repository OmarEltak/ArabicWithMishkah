<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chat_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contract_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('status')->default('draft'); // draft | finalized | archived
            $table->json('parties')->nullable();
            $table->json('variables')->nullable();
            // Citations to LegalChunk ids used while drafting.
            $table->json('citations')->nullable();
            $table->longText('body');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
