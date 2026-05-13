# Egyptian SPA Review Packet

> Purpose: get a licensed Egyptian lawyer to redline the My-lawyer SPA
> template so we can claim, on the homepage, that **"SPA templates have been
> reviewed and signed off by [Name], Cairo Bar #XXXX, as of [Date]."** That
> single named credential is worth more than every other trust signal we
> can manufacture.

## What's in this packet

```
docs/lawyer-review/spa-egypt/
├── README.md                ← you are here
├── reviewer-brief.md        ← what to check, how to redline
├── intake-form.md           ← capture redlines + signoff
├── samples/                 ← 5 SPA drafts to redline
│   ├── 01-mid-market-equity.md
│   ├── 02-founder-buyout.md
│   ├── 03-pe-secondary-sale.md
│   ├── 04-llc-quotas-transfer.md
│   └── 05-staged-acquisition.md
└── source-articles.md       ← what My-lawyer thinks are the controlling
                                 articles, so the reviewer can verify
```

## How to run the review

1. **Send the lawyer this folder** (or print + PDF). Lead with `reviewer-brief.md`.
2. **They redline the 5 sample drafts** — track changes in Word or annotate
   the markdown directly. Both work; we care about the substance.
3. **They fill in `intake-form.md`** — captures the specific changes,
   citations they accepted, citations they replaced, clauses they added/removed.
4. **They sign + date `intake-form.md`** — that's the artifact we need.
5. **We lock their redlines into the SPA template** at
   `database/seeders/ContractTemplateSeeder.php` and re-seed.
6. **We update the homepage** with "Reviewed by [Name], Cairo Bar #X, [Date]".

## Suggested compensation

A senior associate (5+ years post-qualification) at a mid-size Cairo firm
should be able to redline these 5 SPAs in 3–4 hours. Pay £200–£300 GBP / EGP
equivalent for the work, plus an offer to credit them on the homepage and
in the marketing.

A junior associate (1–3 years) at half the rate also works for a first pass;
have a senior associate sign off at the end. The signed-off-by line is what
matters commercially.

## Why SPA first

Share Purchase Agreements are:
- High-frequency at mid-market Egyptian firms (≥ 1 SPA per month at most active
  shops)
- Long enough to surface real drafting differences (NDAs are too thin to test)
- Citation-rich (Civil Code 418, 429, 147, 148 + Companies Law 159/1981) — gives
  the corpus gate something meaningful to verify
- Where AI drafting has the biggest skeptic problem to overcome

If we get an Egyptian SPA signed off, the path to SHA (shareholders' agreement)
and Asset Purchase next is short.

## Output: what we ship to customers afterwards

- A locked-in template at `database/seeders/...` with the lawyer's redlines
- An `eyebrow-tag` on the SPA contract-type page: **"Reviewed by [Name],
  Cairo Bar #XXXX · :date"**
- A 1-page "How we verify our templates" page linked from the homepage's
  "How we work" section
- A signed PDF of `intake-form.md` retained in our records (not public) for
  legal audit
