# Egypt Launch — 30-day "Boom" Plan

> The single-page operating doc for landing My-lawyer in the Egyptian
> corporate-lawyer market. Read `EGYPT_ICP.md` first for the audience map.

---

## North-star metric for the launch window

**Day-7 retention among Egyptian signups ≥ 35%.**

Why: a lawyer who comes back in week one is one who used the product on a real
client matter, not a curiosity click. Vanity sign-ups don't drive revenue;
return visits do. Everything below feeds this number.

Supporting metrics tracked weekly:

| KPI | Week-1 target | Day-30 target |
|---|---|---|
| Egyptian signups (free tier) | 100 | 800 |
| Activated (first draft finalised) | 35 | 320 |
| Day-7 retention | 35% | 40% |
| Paid conversions (Starter EG or Solo) | 5 | 40 |
| Inbound demos from /eg | 8 | 60 |
| LinkedIn followers (founder + brand) | +200 | +1,200 |

---

## The ORB channel mix (per the launch-strategy skill)

### Owned

- **`/eg`** — Egypt-specific landing (built; see task #57 in CHANGELOG)
- **`/help`** — already shipped, 6 articles
- **`/changelog`** — already shipped
- **Founder LinkedIn + Twitter** — daily post during launch week (templates below)
- **Email list** — start from beta inbox; grow via the free tool's email gate
- **WhatsApp broadcast list** — keep ≤ 256 contacts per list; send weekly tip

### Rented

- **LinkedIn organic** — primary channel for both ICPs. Post 1× daily for 30 days.
- **Twitter/X** — secondary, more for the startup-founder persona
- **Facebook lawyer groups** — Cairo Bar legal-tech subgroup, Egyptian Lawyers Network
- **Telegram channels** — جلسات قانونية + 2 peer-discussion channels (200–500 lawyers each)
- **Reddit r/Egypt + r/legaltech** — light touch, value-first
- **WhatsApp** — Cairo Bar Association alumni groups, AUC law alumni
- **Cairo Legal Tech Meetup** — sponsor next event (~EGP 5k) and present a 10-min demo

### Borrowed

- **Cairo Bar Association events** — present at one
- **Riseup Summit 2026** — apply for startup-stage; aim for the legal-tech track
- **Falak Startups demo day** — if eligible
- **Peer founder podcasts** — Wamda, MAGNiTT, AlSharq (3 pitches sent week 2)
- **Newsletter swap** — Wamda Daily, Egyptian Streets Business, Enterprise (paid co-marketing)

---

## 30-day calendar

Week 1 = soft launch (private invite list). Week 2 = public launch. Weeks 3–4 = optimisation + word-of-mouth.

### Day −7 to 0 — Pre-launch (private)

| Day | Action |
|---|---|
| -7 | Final preflight (`scripts/preflight.sh --strict`); domain + DNS + DKIM/SPF set |
| -6 | Stripe products created; webhook tested in test mode; one real EGP test purchase |
| -5 | Invite 30 hand-picked Cairo lawyers via WhatsApp / DM; 14-day trial code |
| -4 | Build the "first 10 testimonials" pipeline: ask each invitee for a 1-line quote on day 3 of use |
| -3 | Schedule LinkedIn launch post for day 0 (8am Cairo time = morning coffee for the persona) |
| -2 | Sentry / Slack alerting live; on-call rota set for launch week |
| -1 | Founder posts a "tomorrow we launch" teaser on LinkedIn |
| 0 | LAUNCH POST (see assets) on LinkedIn + Twitter + WhatsApp broadcast |

### Week 1 — Public launch (Mon Day 0 → Sun Day 6)

**Daily** (cadence — non-negotiable):
- 8:00 Cairo: LinkedIn post from founder account
- 14:00 Cairo: 1 reply to every comment from the morning
- 20:00 Cairo: Twitter post (English) + WhatsApp broadcast to one segment
- All week: in-app live chat manned (or Intercom) — no silent failures

**Themes by day:**

| Day | Theme | Concrete asset |
|---|---|---|
| Mon | Launch announcement | "We built the drafting tool we wished existed" — 800-word LinkedIn post (template below) |
| Tue | The corpus gate | Screenshot of a draft with unverified citations flagged — "Why your last contract probably has a fabricated citation" |
| Wed | Bilingual demo | 30-sec video: paste Arabic → side-by-side English in 10s |
| Thu | A real saving | Customer quote (use day-3 testimonial): "I drafted my 6 NDAs in 22 minutes instead of 4 hours" |
| Fri | EGP pricing reveal | "We made it cost less than one billable hour. Here's why." |
| Sat | Quiet — weekend in Egypt | Repost top reply of the week with founder's commentary |
| Sun | "What's coming next" teaser | Roadmap glimpse — DocuSign integration / matters / etc. |

### Week 2 — Amplification

- Submit to **Product Hunt** (Tue launch slot — 12:01am PST = 10:01am Cairo)
- Submit to **Wamda Daily, MAGNiTT, Enterprise.press** as guest pitches
- Apply to speak at next **Cairo Legal Tech Meetup** (~30 days out)
- Run **LinkedIn ads** — $300/wk budget, target by job title ("محامي" OR "lawyer" OR "legal counsel") + EG geo
- Send the **7-day cold email** sequence (see assets) to a curated list of 100 Cairo law firms (manually researched, NOT scraped lists — quality > volume)
- Launch the **referral program** (Settings → Refer) — 30% off for 3 months for both sides

### Week 3 — Community

- Sponsor / present at one **Cairo Legal Tech Meetup** (10-min demo)
- Start a **monthly "MENA Legal AI" Office Hours** webinar — first one Day 21
- Launch the **free tool** (`/tools/contract-preview`) as a standalone lead magnet
- Reach out to 5 **AUC Law / Cairo Law** alumni-chapter admins about a 20-min talk

### Week 4 — Iterate from data

- Cohort analysis: who actually came back day 7? What did they draft? Double down on that segment.
- Kill the weakest weekly post type (engagement was lowest).
- Publish the first **Wrapped** post: "Week 1 in numbers — N signups, X drafts, Y citations verified". Pure proof.
- Send the first **product update email** to the list: 3 features added, 2 fixes, 1 customer story.

---

## Asset inventory — what's in `docs/marketing/`

| File | Status | Purpose |
|---|---|---|
| `EGYPT_ICP.md` | ✓ | Audience, competitors, willingness-to-pay anchors |
| `EGYPT_LAUNCH.md` | This file | 30-day operating doc |
| `linkedin-launch.md` | ✓ | Founder LinkedIn launch post (Arabic + English) |
| `cold-email-cairo-firms.md` | ✓ | 7-day sequence to a researched list |
| `whatsapp-templates.md` | ✓ | 4 templates for broadcast + 1:1 follow-up |
| `product-hunt-launch.md` | ✓ | Maker post + tagline + first-hour comment plan |
| `referral-page-copy.md` | ✓ | `/refer` page copy + share assets |
| `social-content-week-1.md` | ✓ | 14 LinkedIn / Twitter post templates (one per slot) |

---

## "What if it doesn't boom?" — Tripwires

If by **Day 14** any of the following is true, pivot:

| Tripwire | Probable cause | Pivot |
|---|---|---|
| <50 signups by Day 14 | Wrong audience or wrong channel | Drop LinkedIn ads; double-down on WhatsApp peer broadcasts |
| 50+ signups but <10% activation | Onboarding friction | Pull a 3-lawyer hallway test; identify the drop-off |
| 10+ activated but Day-7 retention < 20% | Product gap | Run 5 churned-user interviews; ship the single most-asked feature |
| Activations strong but zero paid | Pricing or value-prop mismatch | A/B the /pricing CTA; consider a per-contract pay-as-you-go ($5/draft) option |
| Paid conversions but no word-of-mouth | Referral incentive too weak | Bump referral discount from 30% → 50% for two months |

---

## What success looks like Day 30

- ≥ 800 Egyptian signups
- ≥ 40 paying customers (Starter EG or Solo, no Firm yet)
- ≥ 35% Day-7 retention
- ≥ 5 customer quotes usable as testimonials
- 1 piece of earned press (Wamda / Enterprise / MAGNiTT)
- 1 sponsored / spoken event in the calendar
- A repeatable weekly motion: Monday post, Wednesday demo, Friday office hours, monthly Wrapped

If we hit those numbers, raise the price by 20% in month 2 (anchored to Egypt's accelerating inflation; lawyers expect this). If we miss them by half, run a tripwire pivot.

---

## Who does what

| Role | Owner | Time/week |
|---|---|---|
| LinkedIn founder content | Founder | 5 hrs |
| LinkedIn brand page + community engagement | Marketing lead | 8 hrs |
| Cold email + sales follow-up | Founder (Wk 1–4) → SDR (M2+) | 6 hrs |
| Customer onboarding calls (15 min each) | Founder | 4 hrs |
| WhatsApp broadcasts | Marketing lead | 2 hrs |
| Free tool / lead-magnet build | Engineering | one-time 8 hrs |
| Programmatic SEO Arabic pages | Engineering | one-time 6 hrs |
| Support inbox monitoring | Founder + on-call | rolling |

**Total founder time during launch month: ~20 hrs/week on growth.** The rest is engineering + product polish responding to feedback.
