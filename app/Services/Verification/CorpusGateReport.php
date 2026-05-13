<?php

declare(strict_types=1);

namespace App\Services\Verification;

/**
 * Immutable result of a CorpusCitationGate run.
 *
 * Consumed by:
 *   - ContractDraftingService — to gate draft persistence
 *   - The Lawyer dashboard's citation-audit panel
 *   - Tests
 *
 * Each citation result has a status ∈ {verified, uncertain, unverified}.
 */
final readonly class CorpusGateReport
{
    /**
     * @param  array<int, array{raw:string, article:?string, law:?string, jurisdiction:?string, score:int, status:string}>  $results
     */
    public function __construct(
        public array $results,
        public ?string $jurisdiction = null,
    ) {}

    public function totalCitations(): int
    {
        return count($this->results);
    }

    public function verifiedCount(): int
    {
        return $this->countByStatus('verified');
    }

    public function uncertainCount(): int
    {
        return $this->countByStatus('uncertain');
    }

    public function unverifiedCount(): int
    {
        return $this->countByStatus('unverified');
    }

    /**
     * Pass = no unverified citations. Uncertain ones still pass but are
     * surfaced to the user for review.
     */
    public function passes(): bool
    {
        return $this->unverifiedCount() === 0;
    }

    /**
     * Returns 0..100 percentage of citations that are at least "uncertain".
     */
    public function confidencePercent(): int
    {
        $total = $this->totalCitations();
        if ($total === 0) {
            return 100;
        }
        $passing = $this->verifiedCount() + $this->uncertainCount();

        return (int) round($passing / $total * 100);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function unverifiedCitations(): array
    {
        return array_values(array_filter($this->results, fn ($r) => $r['status'] === 'unverified'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function uncertainCitations(): array
    {
        return array_values(array_filter($this->results, fn ($r) => $r['status'] === 'uncertain'));
    }

    /**
     * Brief human-readable summary for logs and audit-trail entries.
     */
    public function summary(): string
    {
        return sprintf(
            'citations: %d total, %d verified, %d uncertain, %d unverified (%d%% confidence)',
            $this->totalCitations(),
            $this->verifiedCount(),
            $this->uncertainCount(),
            $this->unverifiedCount(),
            $this->confidencePercent(),
        );
    }

    private function countByStatus(string $status): int
    {
        return count(array_filter($this->results, fn ($r) => $r['status'] === $status));
    }
}
