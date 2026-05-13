<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds a translations JSON column for storing working translations of
     * the contract body in non-Arabic languages. Shape:
     *
     *   { "en": { "body": "...", "model": "gemini-2.5-flash",
     *             "generated_at": "2026-05-08T...", "glossary_jurisdiction": "EG" },
     *     "fr": { ... } }
     *
     * Translations are non-binding working aids; the Arabic body remains the
     * legally controlling source.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            $table->json('translations')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            $table->dropColumn('translations');
        });
    }
};
