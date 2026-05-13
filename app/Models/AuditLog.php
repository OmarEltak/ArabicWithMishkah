<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    // Explicit fillable list — never use $guarded = [] on integrity-critical
    // rows. chain_index/prev_hash/content_hmac are written by AuditLogger
    // only and must never be overwritten from request payloads.
    protected $fillable = [
        'chain_index',
        'prev_hash',
        'user_id',
        'subject_type',
        'subject_id',
        'action',
        'summary',
        'metadata',
        'ip',
        'user_agent',
        'content_hmac',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'chain_index' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Audit rows are append-only. Block updates to any persisted row so a
     * compromised admin code path can't tamper with the HMAC chain.
     */
    protected static function booted(): void
    {
        static::updating(function (self $model): void {
            throw new \RuntimeException(
                'AuditLog rows are append-only; updating a persisted audit row is not permitted.'
            );
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
