<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('status')->default('drafting'); // drafting | clarifying | drafted | archived
            $table->json('collected_facts')->nullable();
            $table->json('open_questions')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chat_session_id')->constrained()->cascadeOnDelete();
            $table->string('role'); // user | assistant | system | tool
            $table->longText('content');
            $table->json('metadata')->nullable(); // citations, tool calls
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_sessions');
    }
};
