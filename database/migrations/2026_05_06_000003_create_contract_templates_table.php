<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable(); // e.g. nda, employment, services
            $table->string('jurisdiction')->nullable();
            $table->string('language', 8)->default('en');
            $table->text('description')->nullable();
            // Required input fields (parties, dates, amounts, etc.) drives the questionnaire.
            $table->json('required_fields')->nullable();
            // Plain-text or markdown skeleton with {{placeholder}} variables.
            $table->longText('body');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_templates');
    }
};
