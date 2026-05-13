<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageEvent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'cost_micros' => 'integer',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'cache_read_tokens' => 'integer',
        'cache_creation_tokens' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCostUsdAttribute(): float
    {
        return $this->cost_micros / 1_000_000;
    }
}
