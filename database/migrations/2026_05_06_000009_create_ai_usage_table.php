<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_events', function (Blueprint $table): void {
            $table->id();
            // user_id is nullable for system-driven calls (eastlaws scheduled
            // refreshes, embeddings on system-seeded docs).
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // 'anthropic.chat' | 'voyage.embed' | 'openai.embed' | 'eastlaws.fetch'
            $table->string('provider', 32);
            $table->string('operation', 32);
            $table->string('model', 64)->nullable();

            // Token-style counters. NULL when not applicable (eastlaws has none).
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            // Some providers expose these separately for caching billing.
            $table->unsignedInteger('cache_read_tokens')->nullable();
            $table->unsignedInteger('cache_creation_tokens')->nullable();

            // Cost is computed at write-time using the rate table in
            // config/lawyer.php so historical events stay accurate even when
            // we change pricing later. Stored in micro-USD for integer math.
            $table->unsignedBigInteger('cost_micros')->default(0);

            // 'success' | 'error' | 'rate_limited'
            $table->string('status', 16)->default('success');
            $table->text('error_message')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['provider', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_events');
    }
};
