<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * matters — per-lawyer client/matter folders.
 *
 * A "matter" is the standard organizing unit in a law practice: one
 * client may have several matters (Acme Inc → "Q2 vendor onboarding",
 * "California acquisition"). Contracts and chat sessions can be
 * (nullable) tagged to a matter so the lawyer can filter their work.
 *
 * Why nullable on the FK and why an opt-in:
 *   - Existing contracts / sessions have no matter and that's fine —
 *     "Unassigned" is a valid state forever.
 *   - The lawyer creates matters lazily as they accumulate enough work
 *     per client to justify the bucket.
 *
 * Status enum is small and pragmatic; not a state machine because the
 * lawyer just wants a way to archive completed matters.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('client_name');
            $table->string('matter_name');
            $table->string('reference')->nullable();     // optional internal ref ("ACME-2026-01")
            $table->string('status')->default('active'); // active | archived
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'updated_at']);
            $table->index(['user_id', 'client_name']);
        });

        Schema::table('contracts', function (Blueprint $table): void {
            $table->foreignId('matter_id')->nullable()->after('contract_template_id')->constrained()->nullOnDelete();
            $table->index(['matter_id']);
        });

        Schema::table('chat_sessions', function (Blueprint $table): void {
            $table->foreignId('matter_id')->nullable()->after('contract_template_id')->constrained()->nullOnDelete();
            $table->index(['matter_id']);
        });
    }

    public function down(): void
    {
        Schema::table('chat_sessions', function (Blueprint $table): void {
            $table->dropForeign(['matter_id']);
            $table->dropIndex(['matter_id']);
            $table->dropColumn('matter_id');
        });
        Schema::table('contracts', function (Blueprint $table): void {
            $table->dropForeign(['matter_id']);
            $table->dropIndex(['matter_id']);
            $table->dropColumn('matter_id');
        });
        Schema::dropIfExists('matters');
    }
};
