# Unit Economics — My-lawyer

> What it costs us to serve one user for one month, what we charge,
> and the margin we keep. Re-run when model prices or infra bills change.

**Pricing assumptions (May 2026, USD per 1M tokens):**

| Model | Use | Input | Output | Cache read |
|---|---|---:|---:|---:|
| Gemini 2.5 Pro | Main drafting + edits | $1.25 | $10.00 | $0.31 |
| Gemini 2.5 Flash | Clarifying turns, fact extraction | $0.30 | $2.50 | — |
| Voyage-3 | Embeddings | $0.06 | — | — |

**EGP → USD baseline:** 47.5 EGP / USD (CBE indicative mid rate, May 2026).
Move ±10% to model FX swings.

---

## 1. Cost of one draft

Every draft generation walks through these steps. The token estimates come
from the actual prompts and the avg contract length we ship.

### Typical draft (most common path)

| Stage | Tokens | Cost |
|---|---:|---:|
| 2 clarifying turns (Flash JSON, 1.5k in + 0.4k out each) | 3,800 | $0.0019 |
| Pro draft generation, full-prompt | 12,000 in / 4,500 out | $0.0600 |
| Cached portion (system prompt + retrieved law on rerun) | 5,000 in cached | $0.0016 |
| Citation-verifier query embeddings | 500 | $<0.0001 |
| **Total per typical draft** | | **~$0.064** |

### Edit on an existing draft (cheaper — no clarifying, no tool loop)

| Stage | Tokens | Cost |
|---|---:|---:|
| Pro edit pass (existing body + instruction in, full body out) | 8,000 in / 5,000 out | $0.060 |
| **Total per edit** | | **~$0.060** |

### Worst-case single draft (5 clarifying turns + 4 tool-use rounds + 3 edits)

| Stage | Tokens | Cost |
|---|---:|---:|
| 5 clarifying turns (Flash) | 13,000 | $0.005 |
| 4 tool-use rounds Pro draft | 60,000 in (+ 24,000 cached) + 6,000 out | $0.158 |
| 3 follow-on edits | 30,000 in / 18,000 out | $0.218 |
| **Total worst single contract** | | **~$0.37** |

> One contract maxing every fallback path costs us ~37¢ of AI compute.

---

## 2. Cost per user, per month — worst case

Assumption: **the user maxes out their plan's draft quota AND does 3 edits
per draft.** That's the realistic ceiling, not the theoretical one.

| Plan | Drafts/mo | Cost per draft (with edits) | Monthly AI cost (worst) | Plan price USD | Margin USD | Margin % |
|---|---:|---:|---:|---:|---:|---:|
| **Free** | 3 | $0.24 | **$0.73** | $0 | −$0.73 | — |
| **Solo** | 25 | $0.24 | **$6.11** | $50.53 (2,400 EGP) | +$44.41 | **+88%** |
| **Firm** | 200 | $0.24 | **$48.91** | $166.32 (7,900 EGP) | +$117.41 | **+71%** |
| Enterprise | unlimited | $0.24 × usage | — | custom | depends on negotiated SLA |

**Free is intentionally a loss leader** — at 3 drafts max and 73¢/user
ceiling, you can carry ~5,000 free users for ~$3,650/mo total AI bill.

---

## 3. Infrastructure cost — per month (fixed + per-user-variable)

### Fixed (you pay this whether you have 1 user or 1,000)

| Item | Provider | Plan | Monthly USD |
|---|---|---|---:|
| Web hosting | Forge + DigitalOcean droplet | 2 GB | $18 |
| PostgreSQL + pgvector | DigitalOcean Managed DB | 1 GB | $15 |
| Redis | DigitalOcean Managed Redis | 256 MB | $10 |
| Email | Postmark | 10k emails/mo | $15 |
| Error monitoring | Sentry | Team | $26 |
| Uptime monitoring | Better Stack | Basic | $0 (free tier) |
| Domain | Cloudflare Registrar | .com renewal amortized | $1 |
| Object storage (contract PDFs) | DO Spaces | 250 GB | $5 |
| **Fixed total** | | | **~$90 / month** |

### Variable (scales with user count)

| Item | Cost basis | Per active user / mo |
|---|---|---:|
| LLM compute (worst-case Solo) | see §2 | $6.11 |
| Outbound email (welcome + receipts) | ~10 emails/user/mo via Postmark | $0.015 |
| DB row growth + chunk storage | trivial at MB scale | <$0.01 |
| **Variable per Solo user** | | **~$6.15** |

### Breakeven scenario

- Fixed: $90/mo
- A single paying Solo user covers ~$44 of margin → **breakeven at 2 paying Solos**
- A Firm user covers ~$117 of margin → **breakeven at 1 paying Firm**

---

## 4. Margin sensitivity table

What happens to Solo plan margins if our cost assumptions are wrong:

| Scenario | Cost / draft | Worst monthly cost (25 drafts × 4 turns) | Solo price | Margin % |
|---|---:|---:|---:|---:|
| Best case (cache hits 70%) | $0.10 | $2.50 | $50.53 | **+95%** |
| **Baseline (this doc)** | $0.24 | $6.11 | $50.53 | **+88%** |
| 2× actual token use | $0.49 | $12.22 | $50.53 | +76% |
| 5× actual token use | $1.22 | $30.55 | $50.53 | +40% |
| Pro pricing doubles | $0.49 | $12.22 | $50.53 | +76% |
| FX shock: EGP −20% | $0.24 | $6.11 | $40.42 | +85% |

**Solo stays profitable until cost per draft is ~6× current estimate** — that's a
lot of headroom.

Firm plan sensitivity is tighter (200 drafts amplifies cost):

| Scenario | Cost / draft | Worst monthly | Firm price | Margin % |
|---|---:|---:|---:|---:|
| **Baseline** | $0.24 | $48.91 | $166.32 | **+71%** |
| 2× tokens | $0.49 | $97.82 | $166.32 | +41% |
| 3× tokens | $0.73 | $146.73 | $166.32 | +12% |
| 4× tokens | $0.98 | $195.64 | $166.32 | −18% ⚠️ |

**Firm plan needs careful monitoring.** If average usage on Firm exceeds
3× our model estimate (≈600 drafts equivalent per month per user), the plan
goes negative. Mitigations:
1. Hard rate-limit Firm at 200 drafts/mo (already done — `PlanUsageGate`)
2. Charge per-seat above 5 seats
3. Force draft caching more aggressively

---

## 5. Recommended pricing decision

### Keep the current plans as-is

| Plan | Drafts/mo | Price (EGP) | Price (USD) | Worst margin |
|---|---:|---:|---:|---:|
| Free | 3 | 0 | $0 | −$0.73 (loss leader, fine) |
| Solo | 25 | 2,400 | $50.53 | +88% |
| Firm | 200 | 7,900 | $166.32 | +71% |
| Enterprise | ∞ | custom | — | negotiated |

These prices are healthy. Don't drop them.

### One change to consider: yearly discount

Current yearly is **24,000 EGP** (Solo) — exactly 10× monthly, not 12× monthly.
That's an implicit **17% discount** for paying yearly. Two takes:

- **Keep**: aligns with industry norm (Stripe, Notion, Linear all give ~17% off yearly).
- **Trim to 12% (24,800 EGP)**: still attractive but recaptures $20/year/user
  on cash flow.

Either is defensible. The current 17% is more competitive against Harvey AI
($240/mo or so for similar tier).

### Free-tier sanity check

3 drafts/month is the right cap. Worst-case Free user costs us **73¢/mo**.
We can carry **1,000 free users for ~$730/mo** — which is itself half the
cost of one PR-photo shoot. Free isn't where we lose money; it's marketing.

---

## 6. The 1,000-user scenario

**What does the bill look like at scale?**

Assumptions:
- 5,000 Free users (avg 1.5 drafts/mo, well under cap)
- 200 Solo users (avg 12 drafts/mo, ~half their cap)
- 30 Firm users (avg 80 drafts/mo, ~40% of their cap)
- 3 Enterprise users (avg 500 drafts/mo, custom pricing)

| Bucket | Users | Avg drafts | Cost / user / mo | Total |
|---|---:|---:|---:|---:|
| Free | 5,000 | 1.5 | $0.10 | $480 |
| Solo | 200 | 12 | $2.95 | $590 |
| Firm | 30 | 80 | $19.65 | $590 |
| Enterprise | 3 | 500 | $122.50 | $368 |
| **AI subtotal** | | | | **$2,028** |
| Fixed infra | | | | $90 |
| Variable infra | 5,233 active | | $78 (email + storage) | $78 |
| **Total monthly OpEx** | | | | **~$2,200** |

### Revenue at same scale

| Bucket | Users | ARPU | Revenue |
|---|---:|---:|---:|
| Free | 5,000 | $0 | $0 |
| Solo | 200 | $50.53 | $10,106 |
| Firm | 30 | $166.32 | $4,990 |
| Enterprise | 3 | $625 (avg) | $1,875 |
| **MRR** | | | **$16,971** |

| Metric | Value |
|---|---:|
| **MRR** | $16,971 |
| **OpEx** | $2,200 |
| **Gross profit** | $14,771 |
| **Gross margin** | **87%** |

That's a healthy SaaS margin. The bottleneck at scale isn't cost — it's
**customer acquisition** (the $14,771/mo doesn't include marketing, sales,
or salaries).

---

## 7. Things that would break this model

The unit economics work today. What breaks them:

1. **Gemini Pro price doubles.** Plausible within 24 months. → still
   profitable at 2× (see §4 sensitivity table).
2. **Average draft length doubles to 8k output tokens.** Counterargument:
   `draft_max_tokens` config caps this. Easy fix.
3. **Users discover edit-loops and burn 20+ edits per draft.** Mitigation:
   add `edits_per_draft_limit` in `config/lawyer.php`, default 10. Beyond
   that, charge per-edit or upgrade prompt.
4. **A user runs the model on adversarial prompts** (jailbreaks, very long
   pasted content). Mitigation: token-budget cap per request — already
   in `LAWYER_DRAFT_MAX_TOKENS=8000`.
5. **Massive context windows (>200k tokens) get used.** Gemini Pro charges
   double on >200k tier. Drafts won't hit this; only contract-review
   features (planned, not built) might. Build with the higher tier in
   mind from day one.

---

## 8. Pricing in EGP vs USD — strategic note

We bill in EGP. Our costs are USD-denominated (Gemini, DO, Postmark all
charge in USD). **An EGP devaluation immediately compresses margin.**

| EGP/USD | Solo price USD-equivalent | Margin worst |
|---:|---:|---:|
| 47.5 (today) | $50.53 | +88% |
| 60.0 (−21% EGP) | $40.00 | +85% |
| 75.0 (−37% EGP) | $32.00 | +81% |
| 100.0 (−53% EGP) | $24.00 | +74% |

Even at a catastrophic EGP devaluation, Solo stays comfortably profitable
because AI cost is the dominant line item and that line item is small
relative to plan price.

**Strategic option:** offer USD billing for Firm/Enterprise customers
(many MENA holding cos hold USD anyway). Reduces FX exposure on the
big-ticket plans.

---

## 9. Action items

- [ ] **Today**: confirm Gemini Pro API key works,
      `GEMINI_MODEL=gemini-2.5-pro` set in production `.env`.
- [ ] **This week**: instrument actual draft token counts in dev
      (`UsageTracker` already tracks them) so we can replace these
      estimates with real averages in 30 days.
- [ ] **Before first 50 paying users**: revisit this doc with actual
      usage data, tighten model estimates.
- [ ] **Before scaling past 500 users**: build a real-time per-user cost
      dashboard. Right now `php artisan health:report` shows the totals
      but not per-user.
- [ ] **At 100 paying users**: consider negotiating volume pricing with
      Google for Gemini (typically 15–20% discount above $5k/mo spend).

---

## TL;DR

| Question | Answer |
|---|---|
| What does one draft cost us? | **~$0.06** typical, ~$0.37 worst |
| What does one Solo user cost us per month, worst case? | **~$6.11** |
| What's the Solo margin? | **+88%** at the cap, higher at typical usage |
| What does one Firm user cost us, worst case? | **~$48.91** |
| What's the Firm margin? | **+71%** worst case |
| What's our fixed monthly infra cost? | **~$90** |
| How many Solos do we need to break even on infra? | **2 paying Solos** |
| What's gross margin at 200 Solo + 30 Firm + 3 Enterprise? | **~87%** |
| Should we change prices? | **No — the current plans are well-calibrated** |
