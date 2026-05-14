<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalChunk extends Model
{
    // Explicit fillable. Chunks are written by the ingestion pipeline only;
    // never accept user input. Listing columns prevents future code paths
    // from mass-assigning `legal_document_id` and re-parenting a chunk.
    protected $fillable = [
        'legal_document_id',
        'position',
        'content',
        'embedding',
        'embedding_dim',
        'embedding_model',
        'version',
        'superseded_at',
    ];

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
