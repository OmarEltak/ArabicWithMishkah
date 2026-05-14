<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractTemplate extends Model
{
    // Explicit fillable to prevent a user-controlled payload from setting
    // `is_system` true and turning their template into a globally-visible
    // one. `is_system` is intentionally NOT in this list — set it via
    // direct property assignment from server-controlled code only.
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'category',
        'jurisdiction',
        'language',
        'description',
        'body',
        'required_fields',
    ];

    protected $casts = [
        'required_fields' => 'array',
        'is_system' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
