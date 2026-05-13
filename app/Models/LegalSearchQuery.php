<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalSearchQuery extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SEARCHED = 'searched';

    public const STATUS_EXHAUSTED = 'exhausted';

    public const STATUS_RATE_LIMITED = 'rate_limited';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'last_searched_at' => 'datetime',
        'upstream_result_count' => 'integer',
        'ingested_count' => 'integer',
        'search_count' => 'integer',
    ];

    /**
     * Arabic-normalised, lower-cased query suitable for use as a cache key.
     * Strips diacritics, normalises alef forms, unifies ya/ta-marbuta, and
     * collapses whitespace so cosmetic variants of the same search collapse
     * onto a single cache row.
     */
    public static function normalize(string $raw): string
    {
        $s = trim($raw);
        if ($s === '') {
            return '';
        }

        // Strip Arabic diacritics (tashkeel).
        $s = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $s) ?? $s;
        // Strip tatweel (kashida).
        $s = str_replace("\u{0640}", '', $s);
        // Unify alef forms → bare alef.
        $s = preg_replace('/[\x{0622}\x{0623}\x{0625}\x{0671}]/u', "\u{0627}", $s) ?? $s;
        // Alef-maksura → ya.
        $s = str_replace("\u{0649}", "\u{064A}", $s);
        // Ta-marbuta → ha.
        $s = str_replace("\u{0629}", "\u{0647}", $s);
        // Collapse whitespace.
        $s = trim(preg_replace('/\s+/u', ' ', $s) ?? $s);

        return mb_strtolower($s);
    }

    /**
     * True when the upstream search-list result for this row is still
     * considered fresh (i.e. don't call upstream again).
     */
    public function isCacheFresh(int $ttlDays = 7): bool
    {
        if ($this->last_searched_at === null) {
            return false;
        }

        return $this->last_searched_at->gt(now()->subDays($ttlDays));
    }
}
