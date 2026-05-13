<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds primary `category` (single-domain classifier) and `tags` (multi-domain
 * JSON list) columns to legal_documents. Indexed by (jurisdiction, category)
 * so RAG retrieval can scope efficiently — e.g. when drafting a corporate
 * agreement, only retrieve from category='companies' chunks instead of
 * hitting the entire 27K-chunk pile and risking irrelevant matches.
 *
 * Why both columns?
 *  - `category`  : the document's primary domain. One value, easy to filter
 *    on, fast index. Most queries scope by this.
 *  - `tags`      : opt-in secondary domains. A comprehensive Civil Code
 *    spans sale + lease + family + companies; instead of forcing one
 *    primary, tag all relevant. Keeps the model honest.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_documents', function (Blueprint $table): void {
            $table->string('category', 64)->nullable()->after('jurisdiction');
            $table->json('tags')->nullable()->after('category');
            $table->index(['jurisdiction', 'category']);
        });
    }

    public function down(): void
    {
        Schema::table('legal_documents', function (Blueprint $table): void {
            $table->dropIndex(['jurisdiction', 'category']);
            $table->dropColumn(['category', 'tags']);
        });
    }
};
