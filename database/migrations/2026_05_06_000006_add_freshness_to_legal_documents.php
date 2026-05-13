<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_documents', function (Blueprint $table): void {
            // Hash of the normalized full-text content. Used as the canonical
            // change-detection signal: if a refetch yields a different hash,
            // the document has been amended upstream.
            $table->string('content_hash', 64)->nullable()->after('chunk_count');

            // Optional cheap-detection signal: hash of the eastlaws search-list
            // row HTML. Cheaper to verify (one search call vs one full-text
            // fetch). Falls back to content_hash if not set.
            $table->string('snippet_hash', 64)->nullable()->after('content_hash');

            // Wall-clock timestamps used by the background refresh worker.
            $table->timestamp('last_verified_at')->nullable()->after('snippet_hash');
            $table->timestamp('next_check_at')->nullable()->after('last_verified_at');

            // Tier — drives how often we recheck. Court decisions are immutable
            // by date; constitutions slow; regulations fast.
            $table->string('refresh_policy', 32)->default('standard')->after('next_check_at');

            // Monotonic version counter. Bumped on every detected change.
            $table->unsignedInteger('version')->default(1)->after('refresh_policy');

            $table->index('next_check_at');
            $table->index('content_hash');
        });
    }

    public function down(): void
    {
        Schema::table('legal_documents', function (Blueprint $table): void {
            $table->dropIndex(['next_check_at']);
            $table->dropIndex(['content_hash']);
            $table->dropColumn([
                'content_hash',
                'snippet_hash',
                'last_verified_at',
                'next_check_at',
                'refresh_policy',
                'version',
            ]);
        });
    }
};
