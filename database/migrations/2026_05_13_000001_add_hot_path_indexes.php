<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds indexes on foreign-key columns that are the LHS of common lookups
 * (user → contracts, session → messages, etc.). Laravel's `foreignId()`
 * registers the FK constraint but does NOT create an explicit index on
 * SQLite or PostgreSQL, so these listing queries fall back to a full
 * table scan as the data grows.
 *
 * Safe to run repeatedly — each `addIndexIfMissing` no-ops when the index
 * already exists (different drivers expose this differently, so we use a
 * try/catch rather than a Schema::hasIndex pre-check that doesn't exist
 * in older Laravel releases).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('chat_sessions', ['user_id', 'updated_at'], 'chat_sessions_user_id_updated_at_index');
        $this->addIndexIfMissing('chat_messages', ['chat_session_id', 'id'], 'chat_messages_chat_session_id_id_index');
        $this->addIndexIfMissing('contracts', ['user_id', 'updated_at'], 'contracts_user_id_updated_at_index');
        $this->addIndexIfMissing('contracts', ['chat_session_id'], 'contracts_chat_session_id_index');
        $this->addIndexIfMissing('contracts', ['contract_template_id'], 'contracts_contract_template_id_index');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('chat_sessions', 'chat_sessions_user_id_updated_at_index');
        $this->dropIndexIfExists('chat_messages', 'chat_messages_chat_session_id_id_index');
        $this->dropIndexIfExists('contracts', 'contracts_user_id_updated_at_index');
        $this->dropIndexIfExists('contracts', 'contracts_chat_session_id_index');
        $this->dropIndexIfExists('contracts', 'contracts_contract_template_id_index');
    }

    /**
     * @param array<int, string> $columns
     */
    private function addIndexIfMissing(string $table, array $columns, string $name): void
    {
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $name): void {
                $blueprint->index($columns, $name);
            });
        } catch (\Throwable $e) {
            // Already exists, table missing, or driver doesn't support — skip.
        }
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($name): void {
                $blueprint->dropIndex($name);
            });
        } catch (\Throwable $e) {
            // Index didn't exist — skip.
        }
    }
};
