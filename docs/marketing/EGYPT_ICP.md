# Egypt — ICP, Buyer Map, and Competitive Landscape

> Single source of truth for who My-lawyer is built for in Egypt and what
> they're using today. Everything in marketing/sales should trace back here.

---

## ICP — Three Personas

### 1. **Cairo Solo / Boutique Corporate Lawyer** (PRIMARY)

**Who they are**
- 30–45 years old, qualified at one of: Cairo, Ain Shams, Mansoura, Alexandria
- 4–12 years' experience; either independent or partner-track at a 2–8 lawyer firm in Maadi / Zamalek / 5th Settlement / Smouha
- Practice mix: 60% corporate (incorporation, shareholders agreements, employment, SaaS contracts), 20% real-estate, 20% commercial disputes
- Bilingual (Arabic native + working English); writes contracts in Arabic, communicates with clients in both
- Income: EGP 25,000–80,000/month gross

**Tools they use today**
| Tool | What for | Cost |
|---|---|---|
| Microsoft Word + their own template library | 90% of drafting | sunk |
| Eastlaws subscription | Statute / case-law lookup | ~EGP 12,000–18,000/yr (most lawyers share one login) |
| Google Translate + manual cleanup | Bilingual exports | Free, painful |
| Junior associate / paralegal | First-draft labor | EGP 8,000–15,000/month per head |
| WhatsApp + email | Client comms | Free |

**Pains (verbatim from forums + LinkedIn)**
- "أقضي يوم كامل في صياغة عقد روتيني" / "I spend a whole day on a routine contract"
- The bilingual translation step is unbearable — Google Translate butchers civil-law terms (e.g. الفسخ → "termination" when it means "rescission")
- Eastlaws is good for lookup but doesn't help me *draft*
- Cassation citations are the hardest part — clients want references, lawyers have to dig
- Junior associate output needs heavy correction
- Clients comparing fees with Gulf law firms (cheaper outsourcing temptation)

**Trigger moments to buy**
- Lost a small deal because turnaround was too slow
- New client batch from a startup or VC requesting bilingual NDAs in 48hr
- Junior associate quit — needs to scale output without rehiring
- Saw a partner at a peer firm shipping cleaner work in less time

**Where to find them**
- LinkedIn (heavy use; Arabic + English posts)
- Bar Association events (Cairo Bar — نقابة المحامين)
- Local legal-tech meetups (Cairo Legal Tech — ~200 members)
- WhatsApp groups (peer / alumni)
- Telegram (peer legal discussion channels)

---

### 2. **In-House Counsel at Mid-Market Egyptian Firm**

**Who they are**
- 35–50 years old, lead/sole legal officer at a 50–500-employee Egyptian company (manufacturing, fintech, real estate, FMCG)
- 8+ years prior at a law firm before going in-house
- Reports to CEO/CFO; defends an HR/finance/CEO team that generates ~5–15 contract requests per week

**Tools they use today**
- Word template library inherited from their old firm
- Sometimes a "junior" legal officer below them
- DocuSign or paper for signing
- Outsource to outside counsel for litigation only

**Pains**
- Backlog is the chronic problem — every department waits on legal
- Quality is uneven (juniors); they personally review everything
- Compliance updates (Law 151/2020 PDPL, tax updates) — they fall behind
- Board wants metrics (turnaround time, cost per contract); they have none

**Trigger moments**
- Headcount freeze → can't hire more lawyers
- Compliance audit caught a contract gap
- Quarterly review where the legal team is the bottleneck

**Where to find them**
- LinkedIn (more than the Solo persona — they post for inbound roles)
- Industry conferences (RiseUp, Techne, EFG Hermes' One on One)
- General Counsel WhatsApp groups (smaller but high-signal)

---

### 3. **Cairo Startup Founder Drafting Their Own Contracts** (SECONDARY)

**Who they are**
- 25–35 years old, technical/business co-founders of a YC/MAGNiTT-listed startup
- Series-Seed / Series-A range
- Drafts NDAs, employment letters, vendor MSAs themselves; hires outside counsel only for fundraise + litigation
- Bilingual native English speaker; legal-Arabic literacy weak

**Tools they use today**
- LegalTemplates.net / Rocket Lawyer (US templates, not EG-binding)
- A friendly Egyptian lawyer for 1-off questions
- "Vibes" / "It looked fine in Word"

**Pains**
- Every contract they sign is one they can't enforce in an Egyptian court
- Don't want to call a lawyer for every $300 NDA
- Investor asks for a clean cap table; they realize their cofounder agreement is borderline unenforceable

**Trigger moments**
- Fundraise diligence
- Hiring an employee #5+
- Signing a real customer with a procurement team

**Where to find them**
- LinkedIn
- AngelList / Wamda founder lists
- Riseup Summit, Cairo Innovates
- Local accelerator alumni networks (Falak, AUC Venture Lab, Flat6Labs)

---

## Competitive Landscape — What They're Actually Choosing Between

| Competitor | What they are | Where they win | Where they lose | Our angle |
|---|---|---|---|---|
| **Eastlaws** (eastlaws.com) | Subscription legal database. 1990s-era UI, very deep corpus. | Authoritative statute + case lookup. Universally trusted reference. | Pure reference — doesn't draft anything. Doesn't translate. UI is a chore. | "Eastlaws tells you the law; My-lawyer drafts under it." We *use* Eastlaws-grade corpus as our verification gate, so the lookup-vs-draft distinction is real. |
| **Microsoft Word + their template folder** | Sunk cost; what 95% of EG lawyers use. | Zero friction, total control, "good enough". | No bilingual export, no citation verification, no version history beyond Save-As. | Same Word output (we export `.docx`), plus the drafting + citations + bilingual + history they don't have. Position as "the Word template folder that drafts itself." |
| **Junior associate / paralegal** | EGP 8–15k/month of human labor for first drafts. | Familiar; can run errands; client-facing. | Slow; needs heavy correction; can quit. | Not a junior replacement — a junior *force multiplier*. The junior reviews AI output instead of drafting from scratch. |
| **Mahkamty / Sherikati** (Egyptian legaltech) | Mostly form-fill incorporation tools. | Cheap, EG-specific. | Forms only; not a drafting tool; no bilingual; no AI. | We're a tier above — drafting and review, not form completion. Avoid head-to-head; we're complementary. |
| **Harvey AI / Spellbook / Lexis+** | Western legal AI. | Polished UX, lots of features. | English/US-law trained; no Arabic, no MENA jurisdictions, $500+/mo for a Solo lawyer in Cairo. | The "We are not Harvey" anti-positioning: built in Cairo, under MENA civil-law, bilingual-first, priced for the EGP market. |
| **Gulf outsourced drafting shops** (Dubai, Riyadh) | Lower-margin offshore drafting. | Bilingual; volume capacity. | Slow, lacks EG-specific civil-code grounding, brand-risky for the lawyer-client. | Speed + EG-binding output + you stay the author. |

---

## What They Pay Today — Willingness to Pay Anchors

| Anchor | Cost | Why it matters |
|---|---|---|
| Eastlaws subscription (shared) | EGP 12–18k / year (~EGP 1.3k/mo) | Floor — any tool charging more must clearly out-deliver. |
| Junior associate fully loaded | EGP 12–20k / month | Our Solo tier should feel cheap vs. one junior, save one weekend per month |
| Cairo Bar membership + insurance | EGP 5–8k / year | Reference for "annual professional cost" |
| Outsourced bilingual translation | EGP 80–150 per page | One contract export saves ~3 pages of translation cost — Solo plan pays for itself at 3 drafts/mo |

**Implication**: a Solo plan at **EGP 999/mo** is a no-brainer if it saves a single weekend or replaces 10 pages of paid translation. Anchor: "less than one hour of a senior associate's time."

---

## Objections We Will Hear and How to Pre-Empt

| Objection | How to handle |
|---|---|
| "AI invents citations" | Lead with the citation gate. Show the unverified-citation banner in screenshots. |
| "العميل ما يقبل عقد من AI" / Client won't accept AI-drafted contract | Position the AI as a *draft accelerator* — the lawyer signs off. Word output is identical to what they'd produce by hand. |
| "Eastlaws already does this" | "Eastlaws gives you the law. We draft under it. They're complementary." (Many of our users will keep their Eastlaws sub.) |
| "تكلفة بالدولار" / Priced in USD | Lead the /eg landing page with EGP pricing. Egypt-specific Starter tier in EGP. |
| "Data confidentiality — my client list" | Per-user isolation, HMAC audit log, no training on user content, hosted in [region]. |
| "Won't replace my judgement" | "Right — that's why every output cites primary sources you can read in 30s before you sign." |
| "Internet at the office is slow" | The app is light, works on 4G. Drafts queue and run server-side; you can close the tab. |

---

## Sample Customer Language (use verbatim in copy)

From LinkedIn posts, Mostaqel briefs, and Cairo Bar discussion threads:

- "صياغة العقود بتاخد مني وقت طويل"
- "أتمنى تطبيق يلخصلي القانون المدني"
- "Translating contracts for foreign clients is killing me"
- "AI is the future of legal work — but who's building it for *us*?"
- "I need something that knows القانون المصري, not California"
- "Junior associates are expensive and inconsistent"
- "العميل عايز العقد بكرة الصبح"
- "I bill EGP 800/hr; an hour saved on drafting is real money"

---

## Three-Sentence Positioning (Egypt)

> **"My-lawyer is the AI drafting assistant for Cairo's corporate lawyers. Every draft is grounded in the Egyptian Civil Code, the Companies Law, and Cassation jurisprudence — and exports bilingually so you don't waste another weekend on translation. Built in Cairo, for لقانون المصري — not Silicon Valley boilerplate."**

Test in market against:
- "My-lawyer drafts your contracts in Arabic and English, citation-verified."
- "Like having a senior associate who never sleeps, for less than EGP 1,000 a month."
- "The drafting tool your Eastlaws subscription was missing."

Whichever wins the highest LP CTR is the wedge.
