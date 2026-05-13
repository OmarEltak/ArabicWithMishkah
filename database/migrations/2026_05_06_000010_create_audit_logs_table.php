<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Polymorphic target so we can audit any model. Examples:
            //   subject_type='App\\Models\\Contract', subject_id=42
            //   subject_type='App\\Models\\LegalDocument', subject_id=7
            //   subject_type='App\\Models\\ChatSession', subject_id=15
            $table->nullableMorphs('subject');

            // Verb. Free-form so callers can stay specific:
            //   contract.created | contract.updated | contract.finalized | contract.deleted
            //   document.ingested | document.refreshed | document.invalidated
            //   session.started   | session.refused   | session.drafted
            $table->string('action', 64);

            // Optional human-readable summary; structured data lives in metadata.
            $table->string('summary', 255)->nullable();

            // Diff/snapshot/context. Schema-less by design — auditors care
            // about completeness more than queryability.
            $table->json('metadata')->nullable();

            // Network-level provenance.
            $table->string('ip', 64)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamps();

            // nullableMorphs() already creates an index on (subject_type, subject_id).
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
