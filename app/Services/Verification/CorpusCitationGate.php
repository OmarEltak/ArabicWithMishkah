<?php

declare(strict_types=1);

namespace App\Services\Verification;

use App\Models\LegalDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Scans a generated contract draft for citation patterns and verifies each
 * one against the indexed legal corpus.
 *
 * The product's whole moat is "every clause cites a real article." If we
 * silently ship drafts that cite Article X of Law Y when no such article
 * exists in our corpus, lawyers stop trusting the output and the moat
 * collapses. This service is the gate that prevents that.
 *
 * Strategy:
 *   1) Extract citation patterns from the draft text (Arabic + English)
 *   2) Look up each citation against legal_chunks (full-text) + the
 *      legal_documents.metadata->citation_en/citation_ar fields
 *   3) Mark each citation as verified | uncertain | unverified
 *   4) Refuse the draft if ANY citation is unverified — uncertain rows
 *      pass but get flagged for human review
 *
 * Used by ContractDraftingService before persisting the draft, and by
 * tests in tests/Feature/CitationVerifierTest.php.
 */
final class CorpusCitationGate
{
    /** @var int Minimum confidence (0–100) below which we mark unverified. */
    private const VERIFIED_THRESHOLD = 70;
    private const UNCERTAIN_THRESHOLD = 40;

    /** Strong-signal weights. */
    private const ARTICLE_MATCH_SCORE = 75;
    private const LAW_MATCH_SCORE = 40;
    private const JURISDICTION_BONUS = 10;

    /**
     * Run the verifier against a draft text.
     *
     * @return CitationVerificationReport
     */
    public function verify(string $draftText, ?string $jurisdiction = null): CorpusGateReport
    {
        $citations = $this->extractCitations($draftText);
        $results = [];

        foreach ($citations as $cite) {
            $score = $this->scoreCitation($cite, $jurisdiction);
            $status = $this->classifyScore($score);
            $results[] = [
                'raw'         => $cite['raw'],
                'article'     => $cite['article'] ?? null,
                'law'         => $cite['law'] ?? null,
                'jurisdiction'=> $cite['jurisdiction'] ?? $jurisdiction,
                'score'       => $score,
                'status'      => $status,
            ];
        }

        return new CorpusGateReport($results, $jurisdiction);
    }

    /**
     * Pull out citation candidates from the draft. Matches:
     *   - "Article 148 of the Egyptian Civil Code"
     *   - "Articles 246 and 247"
     *   - "Law No. 131 of 1948"
     *   - "Royal Decree No. M/191"
     *   - Arabic equivalents: "المادة ١٤٨"، "للمادتين ٢٤٦ و٢٤٧"، "القانون رقم ١٣١ لسنة ١٩٤٨"
     *
     * @return array<int, array{raw:string, article?:string, law?:string, jurisdiction?:string}>
     */
    public function extractCitations(string $text): array
    {
        $hits = [];

        // English: Article(s) N [of <law>]
        preg_match_all(
            '/\bArticle?s?\s+(?<num>(?:[A-Z]\/)?\d+(?:\s*(?:and|,)\s*\d+)?)\s+(?:of\s+(?:the\s+)?(?<law>(?:[A-Z][a-z]+\s+)+(?:Code|Law|Decree|Act)(?:\s+(?:of|No\.|Number)?\s*\d+(?:\/\d+)?)?))?/u',
            $text, $m, PREG_SET_ORDER
        );
        foreach ($m as $hit) {
            $hits[] = [
                'raw'     => trim($hit[0]),
                'article' => trim($hit['num']),
                'law'     => isset($hit['law']) ? trim($hit['law']) : null,
            ];
        }

        // English: Law No. N of YYYY (or "Federal Decree-Law No. 5 of 1985")
        preg_match_all(
            '/\b(?:Federal\s+(?:Decree-)?Law|Law|Royal\s+Decree|Decree-Law|Beylical\s+Decree)\s+(?:No\.\s*)?(?<num>(?:M\/)?\d+(?:\/\d+)?)(?:\s+of\s+(?<year>\d{4}H?))?/u',
            $text, $m, PREG_SET_ORDER
        );
        foreach ($m as $hit) {
            $hits[] = [
                'raw' => trim($hit[0]),
                'law' => trim($hit[0]),
            ];
        }

        // Arabic: المادة N or المواد N و M — but ONLY when the citation
        // points to an EXTERNAL statute, not the contract's own clauses.
        //
        // Contract bodies in Arabic use "المادة 1:", "المادة 2:" as their
        // own clause headings (equivalent to "Article 1:" in English
        // contracts). Treating those as statute citations produces
        // dozens of false positives every draft. We filter them out by
        // requiring that the citation be preceded by a linking phrase:
        //
        //   "وفقاً للمادة N من القانون..."   — pursuant to article N of the law
        //   "بموجب المادة N من..."           — under article N of...
        //   "طبقاً للمادتين N و M من..."    — in accordance with articles
        //   "كما تنص المادة N..."            — as article N provides
        //
        // The PRESENCE of "من" (from/of) within ~80 characters AFTER the
        // article reference is the strongest signal — it means "of [the
        // law]". A bare "المادة 1:" without "من" is a clause heading.
        preg_match_all(
            '/(?:^|[^\p{Arabic}])(للمادة|للمادتين|للمواد|بالمادة|بالمادتين|بالمواد|المادة|المادتين|المواد)\s+([\d٠-٩]+(?:\s*(?:و|،)\s*[\d٠-٩]+)*)(?<context>[^\n]{0,80})/u',
            $text, $m, PREG_SET_ORDER
        );
        foreach ($m as $hit) {
            $context = $hit['context'] ?? '';
            // Skip clause-heading false positives: bare "المادة N:" or
            // "المادة N —" or "المادة N\n" with no "من <law>" continuation.
            $looksLikeClauseHeading =
                preg_match('/^\s*[:\-—–]/u', $context)          // "المادة 1:"
                && ! preg_match('/\bمن\s+[الأ]?\p{Arabic}/u', $context);

            if ($looksLikeClauseHeading) {
                continue;
            }

            // The strongest external-citation signal: "من ..." within
            // the lookahead. Without it, we're probably looking at a
            // clause heading we couldn't detect by punctuation alone.
            $hasExternalAnchor = preg_match('/\bمن\s+/u', $context);
            // Or starts with one of the linking prefixes (للمادة, بالمادة, etc.)
            $hasLinkingPrefix = in_array(trim($hit[1]), ['للمادة','للمادتين','للمواد','بالمادة','بالمادتين','بالمواد'], true);

            if (! $hasExternalAnchor && ! $hasLinkingPrefix) {
                continue;
            }

            $hits[] = [
                'raw'     => trim($hit[1].' '.$hit[2]),
                'article' => $this->arabicNumeralsToWestern(trim($hit[2])),
            ];
        }

        // Arabic: القانون رقم N لسنة YYYY
        preg_match_all(
            '/(?:القانون\s+(?:الاتحادي\s+)?رقم|المرسوم\s+(?:بقانون|الملكي|السلطاني)?\s*رقم|الفصل)\s+([\x{0660}-\x{0669}\d\/م]+)(?:\s+لسنة\s+([\x{0660}-\x{0669}\d]+(?:هـ)?))?/u',
            $text, $m, PREG_SET_ORDER
        );
        foreach ($m as $hit) {
            $hits[] = [
                'raw' => trim($hit[0]),
                'law' => trim($hit[0]),
            ];
        }

        return $this->dedupeCitations($hits);
    }

    /**
     * Score a citation against the corpus on a 0-100 scale.
     */
    private function scoreCitation(array $cite, ?string $jurisdiction): int
    {
        $articleNum = $cite['article'] ?? null;
        $lawHint = $cite['law'] ?? null;
        $score = 0;
        $articleMatchedInJur = false;

        // 1) Article-number match against legal_chunks.content (the chunks
        //    we seeded carry "Article N — heading" prefixes).
        if ($articleNum !== null) {
            // Split "246 and 247" into individual numbers and verify ANY of them.
            $individualNums = preg_split('/\s*(?:and|,|و|،)\s*/u', $articleNum) ?: [$articleNum];
            $individualNums = array_map(fn ($n) => trim($n), $individualNums);
            $individualNums = array_filter($individualNums);

            foreach ($individualNums as $num) {
                $articleQuery = LegalDocument::query()
                    ->join('legal_chunks', 'legal_chunks.legal_document_id', '=', 'legal_documents.id')
                    ->where(function ($q) use ($num) {
                        $q->where('legal_chunks.content', 'like', 'Article '.$num.' %')
                          ->orWhere('legal_chunks.content', 'like', 'Article '.$num.'—%')
                          ->orWhere('legal_chunks.content', 'like', '%— Article '.$num.'%');
                    });
                if ($jurisdiction) {
                    $jurMatch = (clone $articleQuery)->where('legal_documents.jurisdiction', $jurisdiction)->count();
                    if ($jurMatch > 0) {
                        $score += self::ARTICLE_MATCH_SCORE;
                        $articleMatchedInJur = true;
                        break;
                    }
                }
                // Fallback: matches outside jurisdiction (worth less).
                if ($articleQuery->count() > 0) {
                    $score += self::ARTICLE_MATCH_SCORE - 20; // 55 — uncertain band
                    break;
                }
            }
        }

        // 2) Law-name match against legal_documents.title or
        //    metadata->citation_en/citation_ar.
        if ($lawHint !== null) {
            $lawQuery = LegalDocument::query()
                ->where(function ($q) use ($lawHint) {
                    $q->where('title', 'like', '%'.$lawHint.'%')
                      ->orWhere('metadata->citation_en', 'like', '%'.$lawHint.'%')
                      ->orWhere('metadata->citation_ar', 'like', '%'.$lawHint.'%');
                });
            if ($jurisdiction) {
                $lawQuery->where('jurisdiction', $jurisdiction);
            }
            if ($lawQuery->exists()) {
                $score += self::LAW_MATCH_SCORE;
            }
        }

        // 3) Bonus when article + law both validate in the same jurisdiction.
        if ($articleMatchedInJur && $score >= self::ARTICLE_MATCH_SCORE + self::LAW_MATCH_SCORE) {
            $score += self::JURISDICTION_BONUS;
        }

        return min(100, $score);
    }

    private function classifyScore(int $score): string
    {
        if ($score >= self::VERIFIED_THRESHOLD) {
            return 'verified';
        }
        if ($score >= self::UNCERTAIN_THRESHOLD) {
            return 'uncertain';
        }
        return 'unverified';
    }

    private function arabicNumeralsToWestern(string $s): string
    {
        return strtr($s, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $hits
     * @return array<int, array<string, mixed>>
     */
    private function dedupeCitations(array $hits): array
    {
        $seen = [];
        $out = [];
        foreach ($hits as $h) {
            $key = strtolower(($h['raw'] ?? '').'|'.($h['article'] ?? '').'|'.($h['law'] ?? ''));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $h;
        }
        return $out;
    }
}
