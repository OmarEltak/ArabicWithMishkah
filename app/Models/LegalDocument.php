<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalDocument extends Model
{
    // Explicit fillable. LegalDocument rows are mostly written by the
    // ingestion service via forceFill() (intentional, server-controlled).
    // Listing the columns here makes the writable surface explicit and
    // blocks any future Model::create([...request_payload...]) path from
    // accidentally setting user_id (cross-tenant write) or ingest_status.
    protected $fillable = [
        'user_id',
        'title',
        'source',
        'source_ref',
        'jurisdiction',
        'category',
        'tags',
        'language',
        'metadata',
        'content',
        'chunk_count',
        'ingest_status',
        'ingested_at',
        'content_hash',
        'snippet_hash',
        'last_verified_at',
        'next_check_at',
        'refresh_policy',
        'version',
    ];

    protected $casts = [
        'metadata' => 'array',
        'tags' => 'array',
        'ingested_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'next_check_at' => 'datetime',
        'version' => 'integer',
    ];

    public const INGEST_STUB = 'stub';

    public const INGEST_QUEUED = 'queued';

    public const INGEST_INDEXING = 'indexing';

    public const INGEST_COMPLETE = 'complete';

    public const INGEST_FAILED = 'failed';

    /** True if the document has body + chunks + embeddings ready for retrieval. */
    public function isIngestComplete(): bool
    {
        return ($this->ingest_status ?? self::INGEST_COMPLETE) === self::INGEST_COMPLETE;
    }

    /** True if the row is only a placeholder (title known, body not fetched yet). */
    public function isStub(): bool
    {
        $status = $this->ingest_status ?? self::INGEST_COMPLETE;

        return in_array($status, [self::INGEST_STUB, self::INGEST_QUEUED, self::INGEST_INDEXING], true);
    }

    /**
     * True if the document is past its scheduled re-verification window. Used
     * by the UI to render a "stale" badge and by the scheduler to pick work.
     */
    public function isStale(): bool
    {
        if ($this->next_check_at === null) {
            // Legacy rows or official-source docs we've never verified — treat
            // as stale so the next scheduler run picks them up.
            return $this->isOfficialSource();
        }

        return $this->next_check_at->isPast();
    }

    /**
     * Whether this document comes from an official statutory feed (gazette,
     * cassation court, regulator, primary legal database) — versus user
     * uploads / URL imports / pasted text.
     */
    public function isOfficialSource(): bool
    {
        return in_array(
            (string) $this->source,
            (array) config('legal_sources.official_slugs', ['eastlaws']),
            true
        );
    }

    /**
     * User-visible label for the document's source. Never returns the
     * internal vendor slug (e.g. 'eastlaws') — instead returns a generic
     * professional label routed through config/legal_sources.php.
     */
    public function getSourceLabelAttribute(): string
    {
        $locale = app()->getLocale();
        $key = $locale === 'ar' ? 'label_ar' : 'label_en';
        $config = config("legal_sources.sources.{$this->source}");

        if (is_array($config) && isset($config[$key])) {
            return (string) $config[$key];
        }

        // Unknown sources fall back to a neutral label so we never leak a
        // raw slug to users.
        return $locale === 'ar' ? 'مصدر قانوني' : 'Legal source';
    }

    /**
     * Short user-visible label (badge form). Same masking rule as above.
     */
    public function getSourceShortAttribute(): string
    {
        $locale = app()->getLocale();
        $key = $locale === 'ar' ? 'short_ar' : 'short_en';
        $config = config("legal_sources.sources.{$this->source}");

        if (is_array($config) && isset($config[$key])) {
            return (string) $config[$key];
        }

        return $locale === 'ar' ? 'مصدر' : 'Source';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(LegalChunk::class);
    }
}
