<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds plan + monthly draft-usage tracking to users.
 *
 * Pre-billing scaffold: the free tier has a hard cap on new contract
 * drafts per calendar month so test traffic / abuse can't burn LLM
 * credits unbounded. When real billing ships (Phase 7), the same
 * columns get reused — only the limit values change per plan.
 *
 * Schema:
 *   plan                       Plan slug. One of: free | solo | firm | enterprise
 *   drafts_used_this_period    Counter incremented on every startSession()
 *                              success that produces a new Contract row.
 *                              Edits to existing drafts do NOT count.
 *   usage_period_start         Datestamp of when the current counting
 *                              period began. Counter resets on first
 *                              draft attempt after a calendar month has
 *                              elapsed since this date.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('plan', 32)->default('free')->after('email');
            $table->unsignedInteger('drafts_used_this_period')->default(0)->after('plan');
            $table->date('usage_period_start')->nullable()->after('drafts_used_this_period');
            $table->index('plan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['plan']);
            $table->dropColumn(['plan', 'drafts_used_this_period', 'usage_period_start']);
        });
    }
};
