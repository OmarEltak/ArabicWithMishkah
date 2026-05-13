<?php

declare(strict_types=1);

namespace App\Services\Ingestion;

/**
 * Tiered freshness policy for legal documents.
 *
 * Different classes of legal authority change at very different rates. A
 * constitution might be amended once a decade; a ministerial decree can be
 * superseded in weeks. We tier documents accordingly so the background refresh
 * worker only spends API budget on docs whose change rate justifies it.
 *
 * Court decisions are IMMUTABLE — they're snapshots in time and never change
 * once published. Verifying them is wasted compute.
 */
final class RefreshPolicy
{
    public const IMMUTABLE = 'immutable';

    public const SLOW = 'slow';

    public const STANDARD = 'standard';

    public const FAST = 'fast';

    /**
     * Days between freshness checks. NULL means never re-check.
     */
    public static function intervalDays(string $policy): ?int
    {
        return match ($policy) {
            self::IMMUTABLE => null,
            self::SLOW => 90,
            self::FAST => 7,
            self::STANDARD => 30,
            default => 30,
        };
    }

    /**
     * Compute the timestamp of the next scheduled check.
     */
    public static function nextCheckAt(string $policy, ?\DateTimeInterface $from = null): ?\DateTimeInterface
    {
        $days = self::intervalDays($policy);
        if ($days === null) {
            return null;
        }
        $base = $from ?? now();
        if ($base instanceof \DateTimeImmutable || $base instanceof \Carbon\CarbonImmutable) {
            return $base->modify("+{$days} days");
        }

        // Carbon mutable / DateTime path
        return (clone $base)->modify("+{$days} days");
    }

    /**
     * Best-effort heuristic to classify a freshly-fetched eastlaws document.
     * Court decisions are immutable; everything else gets STANDARD by default
     * and admins can override per-document via the UI later.
     */
    public static function classifyEastlaws(int $recType, string $title): string
    {
        // recType=2 corresponds to court decisions on eastlaws.
        if ($recType === 2) {
            return self::IMMUTABLE;
        }
        $needle = mb_strtolower($title);
        if (str_contains($needle, 'constitution') || str_contains($needle, 'دستور')) {
            return self::SLOW;
        }
        if (str_contains($needle, 'decree') || str_contains($needle, 'قرار') || str_contains($needle, 'لائحة')) {
            return self::FAST;
        }

        return self::STANDARD;
    }

    /** @return array<int, string> */
    public static function all(): array
    {
        return [self::IMMUTABLE, self::SLOW, self::STANDARD, self::FAST];
    }

    public static function isValid(string $policy): bool
    {
        return in_array($policy, self::all(), true);
    }
}
