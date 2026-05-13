<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalClipping extends Model
{
    protected $fillable = [
        'user_id',
        'legal_document_id',
        'legal_chunk_id',
        'title',
        'snippet',
        'note',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }

    public function chunk(): BelongsTo
    {
        return $this->belongsTo(LegalChunk::class, 'legal_chunk_id');
    }
}
