---
title: Citations and the corpus gate
section: Drafting
order: 20
---

Every draft cites statute articles like *"Article 148 of the Egyptian Civil Code"*. The corpus gate checks each citation against the indexed legal database and marks it one of three ways:

- **Verified** — the article exists in the corpus and the wording aligns with the indexed source. Safe to keep.
- **Uncertain** — the citation exists but the snippet around it doesn't clearly correspond. Worth a second pass.
- **Unverified** — we couldn't match the citation at all. Either the article number is wrong, or the corpus doesn't cover it yet. **Remove or correct these before sending.**

The badge in the toolbar shows the verification count. The right-hand citation panel (visible on lg+ screens) lists every reference with its status.

### Why this matters

LLMs occasionally hallucinate citations — they invent article numbers that don't exist. The corpus gate catches that before you sign anything. If your draft passes the gate, every cited article is a real article you can quote in a brief.

### What to do when a citation is unverified

1. Open the **Citation audit** panel (top-right when in lg+ viewport, or via the "needs-review" banner)
2. For each unverified entry, decide:
   - **Wrong number?** Edit the body to correct it. Saving re-runs the gate.
   - **Corpus gap?** Use **Search Laws** to confirm the article exists upstream, then trigger an ingest of that document.
   - **Not actually needed?** Delete the citation from the body.
3. Click **Save** — the badge counts refresh.
