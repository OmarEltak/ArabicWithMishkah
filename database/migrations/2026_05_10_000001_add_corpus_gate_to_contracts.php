<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the corpus_gate JSON column to contracts.
 *
 * The CorpusCitationGate runs after every draft is generated and verifies
 * each citation against the indexed statutory corpus for the chosen
 * jurisdiction. Its report is stored here so the UI can render an
 * "unverified citations" warning panel and the auditor can later prove
 * what the system saw at generation time.
 *
 * Schema:
 *   corpus_gate.passes               bool
 *   corpus_gate.confidence_pct       int (0..100)
 *   corpus_gate.verified             int
 *   corpus_gate.uncertain            int
 *   corpus_gate.unverified           int
 *   corpus_gate.unverified_citations array of {raw, article, law, score, status}
 *   corpus_gate.uncertain_citations  array of {raw, article, law, score, status}
 *
 * Status semantics:
 *   draft         — corpus gate passed, ready for review by counsel
 *   needs_review  — corpus gate failed, contract still saved but flagged
 *   finalized     — counsel signed off
 *   archived      — old version kept for diffs
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            $table->json('corpus_gate')->nullable()->after('citation_audit');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            $table->dropColumn('corpus_gate');
        });
    }
};
