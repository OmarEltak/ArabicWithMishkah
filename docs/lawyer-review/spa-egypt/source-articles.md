# Articles My-lawyer Thinks Govern an Egyptian SPA

This is the AI's working list — what `lookup_law` retrieves and what the
corpus-gate verifier expects to find when checking citations. Flag anything
here that is wrong, outdated, or that should be replaced.

## Egyptian Civil Code (Law 131/1948)

| Article | Topic | Used for |
|---|---|---|
| 147 | Pacta sunt servanda — the contract is the law of the parties | Boilerplate / governing law clause |
| 148 | Performance in good faith | Boilerplate covenant of good-faith negotiation, performance of the SPA |
| 159 | Force majeure | Conditions precedent fall-through / termination on force majeure |
| 160 | Hardship / theory of unforeseen exceptional circumstances | Price adjustment clauses, MAC |
| 418 | Definition of sale | Sale & purchase clause — generic |
| 429 | Seller's warranty against eviction / third-party rights | Title warranty, free-and-clear language |
| 442 | Seller's warranty against hidden defects | Reps & warranties — target |
| 450 | Buyer's obligation to pay the price | Payment + escrow mechanics |
| 696 | Power of attorney basics | Signing by proxy clauses |

## Companies Law (Law 159 of 1981)

| Article | Topic | Used for |
|---|---|---|
| 85 | LLC quota-holder liability + minimum quotaholders | LLC-target SPAs |
| 88 | Quota transfer restrictions | Pre-emption / right of first refusal |
| 90 | Quota register requirements | Closing deliverables — updated register |
| 159 | JSC share register requirements | Closing deliverables for JSC targets |
| 285 | Material disclosure requirements | Reps & warranties |
| 291 | Pre-emptive rights on capital increase | Anti-dilution provisions |

## Capital Market Law (Law 95 of 1992)

| Article | Topic | Used for |
|---|---|---|
| 6 | FRA supervisory authority | Regulatory disclosure clauses |
| 353 | Mandatory tender offer threshold (1/3 of capital) | Listed-target SPAs |

## Commercial Code (Law 17 of 1999)

| Article | Topic | Used for |
|---|---|---|
| 1 | Scope of commercial law | Recital / general |
| 47 | Commercial books retention | Closing covenants — book delivery |

## Procedure and forum

| Source | Topic |
|---|---|
| Code of Civil and Commercial Procedure (Law 13/1968), Art. 49 | Default venue rules |
| Cairo Economic Courts (Law 120/2008) | Default forum for commercial SPAs |
| Egyptian Arbitration Law (Law 27/1994) | Where parties opt for arbitration |

## Privacy / data (where target processes personal data)

| Source | Topic |
|---|---|
| Personal Data Protection Law (Law 151 of 2020), Art. 12 | Cross-border transfer notification — used in IP/data clauses |

## Tax-related (only flagged in conditions precedent, not as governing-law citations)

- Income Tax Law 91 of 2005 + amendments
- Stamp Duty Law 111 of 1980 + amendments

---

## What to flag

- Articles in the list above that **don't exist** or have been renumbered
- Articles that exist but **aren't the right one** for the use described
- **Missing articles** that should be in this list (e.g. specific articles
  on closing-condition failure, on indemnification caps, on currency
  denomination)
- Articles My-lawyer cites in the sample drafts that **aren't in this list
  at all** — those are hallucinations
- **Newer laws** that should override / supplement the 1948 Civil Code or
  1981 Companies Law in modern Egyptian SPA practice

Mark up this file directly with `[wrong: explain]`, `[missing: add X]`,
`[outdated: replaced by ...]` as appropriate.
