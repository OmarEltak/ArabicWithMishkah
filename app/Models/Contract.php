<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    // Explicit fillable to prevent mass-assignment of foreign keys
    // (user_id, contract_template_id) and audit columns (citation_audit,
    // corpus_gate) from request-derived arrays.
    protected $fillable = [
        'user_id',
        'chat_session_id',
        'contract_template_id',
        'matter_id',
        'title',
        'status',
        'parties',
        'variables',
        'citations',
        'citation_audit',
        'corpus_gate',
        'translations',
        'body_history',
        'body',
        'version',
    ];

    protected $casts = [
        'parties' => 'array',
        'variables' => 'array',
        'citations' => 'array',
        'citation_audit' => 'array',
        'corpus_gate' => 'array',
        'translations' => 'array',
        'body_history' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class, 'contract_template_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'chat_session_id');
    }

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }
}
