<?php

declare(strict_types=1);

namespace App\Services\Contracts;

/**
 * Thrown by PlanUsageGate when a draft attempt would exceed the user's
 * monthly cap on their current plan. The Volt component catches this and
 * surfaces a friendly upgrade prompt instead of a generic error.
 */
final class PlanLimitException extends \RuntimeException
{
    public function __construct(
        public readonly string $plan,
        public readonly int $used,
        public readonly int $limit,
    ) {
        parent::__construct(sprintf(
            'Plan limit reached: %d/%d drafts used this period on the %s plan.',
            $used,
            $limit,
            $plan,
        ));
    }
}
