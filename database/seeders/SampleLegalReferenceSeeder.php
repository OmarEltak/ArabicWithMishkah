<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\Ingestion\IngestionService;
use Illuminate\Database\Seeder;

class SampleLegalReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $service = IngestionService::fromConfig();

        foreach (self::samples() as $row) {
            $service->ingestText(
                user: null,
                title: $row['title'],
                content: $row['content'],
                jurisdiction: $row['jurisdiction'] ?? '',
                language: $row['language'] ?? 'en',
                source: 'paste',
                metadata: ['seeded' => true],
            );
        }
    }

    /** @return array<int, array<string, string>> */
    private static function samples(): array
    {
        return [
            [
                'title' => 'Common law: Elements of a contract',
                'jurisdiction' => 'common-law',
                'language' => 'en',
                'content' => <<<'TXT'
At common law, a binding contract requires four elements: (1) offer, (2) acceptance, (3) consideration, and (4) mutual intent to be legally bound. An offer is a clear proposal of terms; acceptance must mirror the offer (the mirror image rule, with the UCC's "battle of the forms" relaxation between merchants); consideration is something of legal value exchanged by both parties; mutual intent is shown by an objective manifestation of agreement.

A material breach excuses the non-breaching party from further performance and entitles them to damages. Damages aim to put the non-breaching party in the position they would have been in had the contract been performed. Specific performance is available where damages are inadequate, particularly for unique goods or real property.

Statutes of frauds typically require certain contracts to be in writing, including contracts for the sale of land, contracts not capable of being performed within one year, and contracts for the sale of goods over a threshold amount.
TXT,
            ],
            [
                'title' => 'NDA best-practice clauses',
                'jurisdiction' => 'common-law',
                'language' => 'en',
                'content' => <<<'TXT'
A well-drafted NDA should: (1) precisely define "Confidential Information"; (2) state a permitted purpose; (3) impose a duty of care no lower than reasonable care; (4) include the standard exclusions (publicly known, prior knowledge, independently developed, lawfully received from a third party); (5) carve out compelled disclosure under law with a notice obligation; (6) state a clear term, distinguishing the term of the agreement from the survival period of confidentiality obligations; (7) require return or destruction on demand; (8) reserve injunctive relief; (9) include governing law and venue.

For trade secrets, jurisdictions following the Defend Trade Secrets Act in the United States require notice of immunity for whistleblower disclosures (18 U.S.C. § 1833(b)) for the discloser to recover exemplary damages or attorneys' fees in trade-secret claims.
TXT,
            ],
            [
                'title' => 'Employment at-will doctrine (US)',
                'jurisdiction' => 'US',
                'language' => 'en',
                'content' => <<<'TXT'
In the United States, employment is presumed at-will absent contrary contract terms. At-will employment may be terminated by either party at any time, with or without cause, subject to statutory and common-law exceptions: anti-discrimination statutes (Title VII, ADEA, ADA), public-policy exceptions, implied-contract exceptions, and the implied covenant of good faith and fair dealing in some states. Some states (notably Montana) have abolished the at-will presumption by statute. Choice-of-law clauses in employment contracts are subject to scrutiny for non-compete reasonableness and public-policy limits.
TXT,
            ],
        ];
    }
}
