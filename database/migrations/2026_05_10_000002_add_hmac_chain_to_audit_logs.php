<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds tamper-evident HMAC chaining to audit_logs.
 *
 * Why: a partner reviewing how an AI-generated clause came about must be
 * able to prove that no row in the audit trail was edited or deleted
 * after the fact. Each new row signs (with HMAC-SHA256) the canonical
 * representation of itself + the previous row's signature. Breaking
 * any single row breaks the chain forever — `audit:verify` will surface
 * the exact row at which the chain diverged.
 *
 *   chain_index    monotonic per-row counter (0, 1, 2, …)
 *   prev_hash      content_hmac of the previous row, hex (or all-zeros for row 0)
 *   content_hmac   hex SHA256 HMAC of the canonical payload using APP_AUDIT_KEY
 *
 * The columns are populated by the AuditLogger at write time. Verification
 * is performed by the audit:verify command.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->unsignedBigInteger('chain_index')->nullable()->after('id');
            $table->string('prev_hash', 64)->nullable()->after('chain_index');
            $table->string('content_hmac', 64)->nullable()->after('prev_hash');

            $table->index('chain_index');
            $table->index('content_hmac');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex(['chain_index']);
            $table->dropIndex(['content_hmac']);
            $table->dropColumn(['chain_index', 'prev_hash', 'content_hmac']);
        });
    }
};
