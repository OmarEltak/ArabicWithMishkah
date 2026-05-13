<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalChunk extends Model
{
    protected $guarded = [];

    protected $casts = [
        'embedding' => 'array',
        'superseded_at' => 'datetime',
        'version' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }
}
