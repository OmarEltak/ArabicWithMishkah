<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            // Per-marker audit results from CitationVerifier:
            //   [
            //     'marker_1' => ['chunk_id' => 12, 'score' => 0.83, 'verdict' => 'verified',
            //                    'document_title' => '...', 'document_version' => 1,
            //                    'snippet' => 'surrounding draft sentence...'],
            //     ...
            //     'tool_chunks_seen' => [12, 13, 14],
            //   ]
            $table->json('citation_audit')->nullable()->after('citations');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            $table->dropColumn('citation_audit');
        });
    }
};
