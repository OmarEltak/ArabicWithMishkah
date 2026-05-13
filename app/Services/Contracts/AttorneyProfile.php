<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use Illuminate\Support\Facades\Cache;

/**
 * Loads jurisdiction-specific senior-attorney profile knowledge that gets
 * prepended to the drafting LLM's system prompt. Acts as a "skill" — domain
 * expertise the base model doesn't reliably have on its own.
 *
 * Profiles live as markdown files under `resources/legal/`. Naming:
 *   senior-attorney-{jurisdiction}.md   (e.g. senior-attorney-eg.md)
 *   senior-attorney.md                  (generic fallback)
 *
 * Content is cached in-process so multiple drafts in one request don't
 * re-read the file. Admins can rotate profiles without a deploy by clearing
 * cache.
 */
final class AttorneyProfile
{
    private const CACHE_TTL = 3600;

    /**
     * Return the system-prompt-ready profile for a jurisdiction. Falls back
     * to the generic profile and finally to an empty string. Truncates if
     * config caps it (some providers have tight prompt budgets).
     */
    public static function for(?string $jurisdiction = null): string
    {
        $key = strtolower((string) $jurisdiction);

        return (string) Cache::driver('array')->remember(
            'attorney_profile.'.$key,
            self::CACHE_TTL,
            function () use ($key): string {
                $candidates = [];
                if ($key !== '') {
                    $candidates[] = resource_path('legal/senior-attorney-'.$key.'.md');
                }
                $candidates[] = resource_path('legal/senior-attorney.md');

                foreach ($candidates as $path) {
                    if (is_file($path)) {
                        $body = (string) file_get_contents($path);
                        $cap = (int) config('lawyer.attorney_profile_max_chars', 8000);
                        if ($cap > 0 && mb_strlen($body) > $cap) {
                            $body = mb_substr($body, 0, $cap)."\n\n[profile truncated to {$cap} chars]";
                        }

                        return $body;
                    }
                }

                return '';
            }
        );
    }
}
