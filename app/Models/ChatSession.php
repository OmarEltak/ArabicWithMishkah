<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    // Explicit fillable to keep user_id (ownership) safe from
    // mass-assignment when payload-derived arrays are passed to create().
    protected $fillable = [
        'user_id',
        'contract_template_id',
        'title',
        'status',
        'collected_facts',
        'open_questions',
    ];

    protected $casts = [
        'collected_facts' => 'array',
        'open_questions' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class, 'contract_template_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
