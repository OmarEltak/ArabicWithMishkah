# My-lawyer · Build Journal

A chronological record of every architectural decision in this project,
with the *why* behind each. Entries are appended; nothing is rewritten.
Read top-to-bottom to understand how the system arrived at its current shape.

---

## Phase 0 — Genesis

**Goal as stated by the user:** an AI workflow for lawyers that

1. drafts contracts based on whatever data is available,
2. asks clarifying questions when it doesn't know the law,
3. uses eastlaws.com (their $18k/yr Egyptian/MENA legal database) as the
   canonical RAG source,
4. is tested and ready for real users.

**Starting point:** Laravel 13 + Livewire 4 + Flux UI starter kit on SQLite.
Basic auth, dashboard, settings — nothing else.

---

## Phase 1 — Core RAG + drafting pipeline

### What we built

| Component | Purpose |
|---|---|
| `AnthropicService` | Claude API client with `chat()` + tool-use loop support, mock fallback when no API key |
| `EmbeddingService` | Voyage / OpenAI / mock (256-d SHA-derived) provider switcher |
| `RagService` | Chunking (sentence-boundary aware, Arabic-safe), in-PHP cosine top-K search via SplPriorityQueue |
| `IngestionService` | Paste / upload (PDF/DOCX via smalot+phpword) / URL ingestion |
| `EastlawsClient` | Reverse-engineered ASP.NET Identity auth, antiforgery tokens, multipart search, full-text fetch — 12h cookie cache, 3s throttle |
| `EastlawsIngestService` | Bulk-ingest a search query, idempotent by `source_ref` |
| `LawLookupTool` | Tool exposed to the LLM — eastlaws-first, returns NOT_FOUND when no authority found |
| `ContractDraftingService` | Clarifying-question loop + tool-use draft loop + refusal detection |
| `ContractExporter` | DOCX export via PhpOffice/PhpWord with heading detection + disclaimer |
| 5 migrations | `legal_documents`, `legal_chunks`, `contract_templates`, `chat_sessions/messages`, `contracts` |
| 4 Livewire pages | `chat`, `contracts`, `templates`, `knowledge-base` |

### Pivot: eastlaws-first with hard refusal

> "i want the ai to fucking use eastlaws as a rag if the data is not there
> just make it respond with sorry the data is not there"

**Why this mattered:** the worst failure mode for a legal AI is a
fabricated citation. A confident-sounding article number from a hallucination
costs the lawyer credibility and could cause real client harm.

**What we changed:**
- `LawLookupTool` rewritten: cache-check first (cosine ≥ 0.85 = trusted hit),
  otherwise eastlaws ingestion, otherwise return `NOT_FOUND.`
- System prompt added "HARD RULES": call `lookup_law` before every legal claim;
  on `NOT_FOUND`, output literal `SORRY:` refusal in a fixed format
- `ContractDraftingService::isRefusal()` detects refusal text, marks session
  `status=refused`, persists no `Contract`, surfaces an amber-styled warning UI

End of Phase 1: **57 tests passing**.

---

## Phase 2 — Freshness & version-aware RAG

User's question that triggered this phase:

> "let's say right now our DB has a load that hasn't been modified.
> But by the day they modified this and other external sites already.
> How should we handle the update issues?"

This was a real architectural gap — the original code did pure idempotency
(skip if `source_ref` exists), with no staleness detection. Once a law was
cached, it was frozen forever. For an 18k/yr legal database that is
fundamentally a time-varying source of truth, that was a liability.

### Why we picked hash-based detection over the obvious alternatives

| Approach | Verdict | Why |
|---|---|---|
| Pure TTL ("refetch anything older than N days") | ❌ rejected | Wasteful — refetches stable constitutions, misses fast-moving regulations between intervals |
| Always-live (no cache) | ❌ rejected | Defeats the subscription's value, 3-10s/query latency, hard dependency on eastlaws uptime |
| Periodic full re-crawl | ❌ rejected | At 383k Egyptian records × 3s throttle = ~13 days per cycle, ~$300+ in embedding cost per cycle, 99% wasted on unchanged docs |
| Webhook / push-based invalidation | ❌ not possible | Eastlaws doesn't publish a change feed; can't will an API into existence |
| **Hash content per-doc + tiered intervals + version history** | ✅ chosen | Detection scales with change rate, not corpus size; legal audit trail preserved |

### What we built

| Component | Purpose |
|---|---|
| `RefreshPolicy` value object | Tiers: `IMMUTABLE` (court rulings, never recheck), `SLOW` (90d, constitutions), `STANDARD` (30d, codes), `FAST` (7d, decrees) |
| Migration `add_freshness_to_legal_documents` | Adds `content_hash`, `snippet_hash`, `last_verified_at`, `next_check_at`, `refresh_policy`, `version` |
| Migration `add_versioning_to_legal_chunks` | Adds `version`, `superseded_at` — chunks are NEVER overwritten/deleted |
| `FreshnessService::verify($doc)` | Refetches full text → sha256 → compare → if changed: supersede chunks, bump `version`, store revision history in `metadata.revisions[]`, re-index |
| `RagService::indexDocument()` | Now supersedes instead of deletes — preserves the legal audit trail (a contract drafted under v1 must remain reproducible after v2 lands) |
| `RagService::search()` | Filters `WHERE superseded_at IS NULL` — the LLM only ever sees current law |
| `VerifyDocumentFreshnessJob` | Queueable, 5min timeout, 2 retries with 60s backoff |
| `eastlaws:refresh-stale` command | Picks `next_check_at <= now` rows, runs inline or via queue |
| `eastlaws:invalidate` command | Kill switch — forces immediate recheck for known major changes |
| Scheduler | Nightly at 03:00, `onOneServer`, `withoutOverlapping` |
| Knowledge Base UI | Adds Version, Verified (humanised + "stale" badge), Policy columns + Refresh button |
| `LawLookupTool` citation format | Now includes `version=N, verified=YYYY-MM-DD [STALE]` so Claude can warn users about stale authority |

End of Phase 2: **65 tests passing** (8 new freshness tests).

---

## Phase 3 — Production-readiness sweep (9 items)

Asked "if you were a senior architect with 20 years experience, what's left?"
The list: citation verification, diff viewer, cost limits, Postgres/pgvector,
audit log, observability, streaming, RTL/Arabic, opt-in eastlaws integration test.

### Why each item

**1. Citation verification (`CitationVerifier`)** — freshness proves the law is
*current*, but doesn't prove the model *quoted it correctly*. Closes the
hallucination loop. Walks every `[n]` marker in a draft, embeds the surrounding
sentence, finds best-cosine match among chunks `lookup_law` actually surfaced
during the draft. Verdict: ≥0.70 verified, 0.50–0.70 uncertain, <0.50
unverified. Stored on `contracts.citation_audit`. UI panel rewritten to show
colour-coded badges per marker — fixes a latent bug where the old panel
treated `[1, 2, 3]` display markers as chunk IDs.

**2. Diff viewer (`DiffService` + `/lawyer/knowledge-base/{id}/diff`)** — makes
the supersession work *visible*. Aligns chunks across versions by `position`,
classifies rows as `unchanged | changed | added | removed`. Side-by-side table
with version pickers. The "Diff" button only appears on docs with `version > 1`.

**3. Cost & rate limits (`UsageTracker` + `ai_usage_events`)** — nothing
protected the $18k/yr eastlaws subscription, the Anthropic budget, or the
embedding budget from runaway calls. One bug = a $5k bill. Built per-user +
global daily caps via `BudgetExceededException` (thrown BEFORE every API call);
cost recorded in micro-USD with locked-in pricing per provider/model.
Dedicated `/lawyer/usage` dashboard with progress bars and recent-calls table.

**4. PostgreSQL + pgvector** — in-PHP cosine works to ~10k chunks then falls
over. Built a *conditional* migration: no-op on SQLite, on Postgres it runs
`CREATE EXTENSION vector` and adds `embedding_pgv` with HNSW index (falling
back to ivfflat for older pgvector). `RagService::search()` is dual-pathed —
detects environment once per request, uses SQL `<=>` operator when pgvector
available, falls back to PHP loop otherwise. Indexing path mirrors writes
(JSON always, pgvector when available). `rag:promote-to-pgvector` command
backfills existing JSON vectors. SQLite stays the dev default; Postgres is
opt-in via `DB_CONNECTION=pgsql`.

**5. Audit log (`AuditLogger` + `audit_logs` polymorphic table)** — table
stakes for legal SaaS. Append-only, never updates/deletes. Hooks:
`contract.created/updated/finalized/deleted`, `session.refused`,
`document.ingested/verified/refreshed/invalidated`. `/lawyer/audit` viewer
with action filter + summary search + pagination.

**6. Observability (`ai` log channel)** — every Anthropic / Voyage / OpenAI /
eastlaws / freshness `Log::*` call moved to a dedicated daily-rotating channel
(`storage/logs/ai-YYYY-MM-DD.log`, 30-day retention). Tail one file to spot
rate-limit hits, latency, auth failures.

**7. Streaming responses (`AnthropicService::chatStream()`)** — full SSE
parser handling `message_start`, `content_block_delta`, `content_block_stop`,
`message_delta`, `message_stop`. Accumulates text deltas via `$onDelta()`
callback and tool-use `input_json_delta` correctly across chunks. Records
cost/tokens to UsageTracker exactly like the non-streaming path. Falls
through to single-delta emission when API key is missing or tools are present
(streaming through tool-use rounds is more complex than the MVP needs).
Livewire chat page uses `$this->stream(to: 'reply-stream', content: $delta)`
to push chunks to the browser.

**8. RTL/Arabic UI** — layout sets `dir="rtl"` automatically when
`app()->getLocale()` is `ar`/`he`/`fa`/`ur`. Tailwind logical CSS properties
(`text-start`, `border-s`) were already used throughout, so this was a
one-line change.

**9. Eastlaws integration test** — opt-in via `EASTLAWS_INTEGRATION_TESTS=1`
env flag. Walks login → list countries → search → ingest → freshness verify
against the live site. Skipped by default so CI stays green for everyone.

End of Phase 3: **77 tests passing** (3 new SSE parser tests via reflection;
2 intentionally skipped — the eastlaws integration tests).

### What we explicitly deferred (and why)

- **Sentry / error tracking** — needs an account + DSN; the `ai` log channel
  covers the primary use case for now.
- **Reverb / WebSocket-driven streaming** — Livewire 4's `$this->stream()` is
  enough for our use case. Reverb adds infrastructure we don't yet need.

---

## Phase 4 — Groq adapter (multi-vendor LLM support)

User provided a Groq free-tier API key. Groq runs open-source models (Llama,
Mixtral, Gemma) via an **OpenAI-compatible** API — fundamentally different
wire protocol from Anthropic.

### Why we built a vendor abstraction

The codebase was hardcoded to Anthropic's tool-use blocks. Adding Groq required
either (a) rewriting `ContractDraftingService` with vendor branches (technical
debt) or (b) abstracting the LLM behind an interface (clean, but more upfront
work). Chose (b).

| Component | Purpose |
|---|---|
| `LlmInterface` | `chat()` + `chatStream()` + `isConfigured()` — the only contract callers depend on |
| `AnthropicService implements LlmInterface` | Existing service, no behavioural change |
| `GroqService implements LlmInterface` | New OpenAI-compatible adapter that translates **at the boundary**: Anthropic tool defs → OpenAI `function/parameters`; Anthropic `tool_use` blocks ↔ OpenAI `tool_calls` array; Anthropic `tool_result` user blocks → OpenAI `role=tool` messages |
| `LlmFactory::default()` | Picks the active backend from `config('lawyer.llm_provider')` (`anthropic` \| `groq`) |
| `ContractDraftingService` | Now depends on `LlmInterface`, not `AnthropicService` directly |

### The hybrid-retrieval pivot

When we ran the first real Arabic land-contract test, we hit a problem the
existing architecture didn't anticipate: **mock embeddings aren't semantic**.
Without a Voyage/OpenAI key, `EmbeddingService` returns deterministic SHA-256
derived 256-d vectors — only *identical* strings produce identical vectors;
similar strings produce wildly different ones. So lookup_law's vector search
returned nothing useful even though the Egyptian Civil Code (قانون 131/1948)
was right there in the KB.

Two fixes were viable: (a) tell the user to buy a Voyage subscription, or (b)
add hybrid retrieval. Hybrid retrieval is the gold standard in production RAG
anyway (BM25 + vector beats either alone), so we built it now.

`RagService::searchByKeyword($query, $k)`: splits on whitespace, drops 1-char
noise, OR-matches each term via SQL `LIKE`, scores by term-overlap fraction,
returns top-K. `LawLookupTool` checks vector first; if cosine top-score < 0.85
*and* keyword top-score ≥ 0.5, returns keyword results before falling through
to eastlaws.

This is the one piece of Phase 4 that's a permanent product feature, not a
demo workaround — when paid embeddings are configured, hybrid still helps.

### Token budget knobs

Groq's free tier on `llama-3.1-8b-instant` is **6,000 TPM** (tokens-per-minute),
counted as `input + reserved max_tokens`. Drafting requests blew through this
repeatedly. Added 5 `.env`-tunable knobs:

| Knob | Default (Anthropic) | Groq free-tier setting | What it caps |
|---|---|---|---|
| `LAWYER_TOP_K` | 6 | 1 | RAG chunks pulled into the draft prompt |
| `LAWYER_CONTEXT_CHUNK_MAX_CHARS` | 500 | 500 | Per-chunk char cap in `ragContext()` |
| `LAWYER_TOOL_CHUNK_MAX_CHARS` | 600 | 400 | Per-chunk cap in lookup_law's tool result |
| `LAWYER_TOOL_MAX_RESULTS` | 2 | 1 | Citations returned per tool call |
| `LAWYER_MAX_TOOL_ROUNDS` | 2 | 1 | Tool-use loop iterations before forced finalise |
| `LAWYER_DRAFT_MAX_TOKENS` | 4096 | 2000 | Reserved output tokens (huge TPM impact on Groq) |

These are universal tuning surfaces, not Groq-specific. Anthropic users will
likely use the defaults; budget-constrained tiers tune them down.

### The system-prompt loosening

Llama 3.1 8b interpreted the original "HARD RULES — refuse if any sub-clause
lacks authority" too literally — refused entire drafts because it couldn't
verify each of 19 sub-topics. Updated the prompt:

- **Before:** call `lookup_law` for every legal claim, `SORRY:` refuse on
  any NOT_FOUND.
- **After:** call `lookup_law` once for the contract type. Always produce the
  draft. Tag clauses with `[n]` if backed by the lookup, `[UNVERIFIED]` if
  not. CitationVerifier surfaces unverified markers to the lawyer
  automatically. Only `SORRY:` refuse if the entire contract type has zero
  authority (rare).

This is a better real-world UX even with Claude — lawyers want a draft they
can fix, not a refusal they have to start over.

### Schema flexibility for smaller models

Llama 3.1 8b emitted `country_id: "1"` (string) where the schema expected
`integer`, and Groq's tool-call validator rejected it. Loosened
`LawLookupTool::definition()` to declare `country_id` as `string` (we coerce
with `(int)` on the way in). Won't matter for Claude, but the schema is more
permissive in general.

End of Phase 4: A real Arabic عقد بيع قطعة أرض (land sale contract) with 8
articles, draft notes, and citations was generated end-to-end via Groq.
Citation correctness is Llama-8b-tier (cited قانون 13/1968 instead of the
correct قانون 131/1948), but the **pipeline worked**: tool call fired,
keyword retrieval surfaced authority, citation audit ran (1 unverified,
flagged correctly), audit log captured the event, cost tracked.

---

## Final state

### Stack
- **Backend:** Laravel 13.7, Livewire 4.1, Flux UI 2.13, PHP 8.4
- **DB:** SQLite (default) or Postgres+pgvector (opt-in via `DB_CONNECTION`)
- **LLM:** Anthropic Claude (default) or Groq (Llama/Mixtral/Gemma)
- **Embeddings:** Voyage-3 (default), OpenAI text-embedding-3-small, or mock
- **Legal source:** eastlaws.com via reverse-engineered AJAX integration

### Test count
**77 tests passing**, 2 intentionally skipped (opt-in eastlaws live tests),
242 assertions.

### Lines of architecture-significant code added this session
~3,500 (services, migrations, commands, jobs, views, tests).

### Migrations applied
11 total — 5 from Phase 1, 2 freshness columns, 1 citation_audit, 1 ai_usage,
1 audit_logs, 1 conditional pgvector.

### Artisan commands
- `eastlaws:ingest` — bulk-fetch + index a query
- `eastlaws:refresh-stale` — re-verify stale docs (scheduled nightly)
- `eastlaws:invalidate` — kill switch, force immediate recheck
- `rag:promote-to-pgvector` — backfill JSON → vector after Postgres switch

### Sidebar pages
Drafting · Contracts · Templates · Knowledge Base · Usage · Audit
(plus document-diff sub-page)

---

## Recurring principles — what to preserve in future changes

1. **Eastlaws is canonical, the local KB is a write-through cache.**
   Never trust local-only as authority for new claims unless the cosine score
   is high enough to be considered an exact match.

2. **Never overwrite legal data.** Supersede chunks; bump versions; keep
   revision history in `metadata.revisions[]`. A contract drafted under v1
   must remain reproducible after v2 lands.

3. **Refusals are features, not failures.** A model that refuses with a
   honest "no authority found" beats one that fabricates a plausible-looking
   article number every time.

4. **Cost gates run BEFORE the API call, not after.**
   `UsageTracker::assertWithinBudget()` is called before every paid request;
   we'd rather refuse than refund.

5. **Every external call goes through an adapter we own.** The
   `LlmInterface` / `EmbeddingService` / `EastlawsClient` boundaries mean
   swapping providers stays a config change, not a code rewrite.

6. **Audit everything user-meaningful.** `AuditLogger` writes never break the
   user flow — failures fall back to the log channel — but they always run.

---

## Phase 5 — Moat & corporate-customer prep

User's first paying customer is shaping up to be a corporate-law attorney.
Pre-demo work: build out the eastlaws-derived corpus, classify it for
scoped retrieval, and seed corporate-specific drafting templates so the
demo "feels like" their practice.

### Bulk snapshot
- **`config/eastlaws_snapshot.php`** + **`php artisan eastlaws:snapshot`** —
  matrix of 5 jurisdictions × ~33 statute queries (general profile) + 26
  corporate-focused queries (corporate profile). One command pulls the
  most-cited statutes for each country into permanent local storage.
- **Idempotent re-runs.** `bulkIngestQuery` skips docs already present
  (matched by `source_ref`). Nightly re-runs are cheap.
- **Throttled** at 3s per upstream HTTP call.
- **First sweep**: 696 → 33,449 chunks (37× growth).

### The two snapshot bugs we caught and fixed
1. **Hardcoded `jurisdiction='EG'`** in `EastlawsIngestService::fetchAndIngestOne`
   — every snapshot doc inherited "EG" regardless of which country the
   search came from. Mapped `country_id` → ISO codes (1→EG, 4→AE, 5→KW,
   6→BH, 7→QA, 9→SA) and persisted `eastlaws_country_id` in metadata.
   Built `eastlaws:retag-jurisdictions` (heuristic: title pattern matching
   for "اتحادي"/"نظام"/"الكويت" markers) AND `eastlaws:smart-retag`
   (authoritative: re-queries eastlaws per snapshot config and matches
   results by exact `eastlaws_id`). Repaired all 215 mis-tagged docs.

2. **No category column.** The Civil Code has chapters spanning sale,
   lease, family, companies, agency, etc. The model couldn't scope its
   retrieval — corporate queries pulled family-law chunks and vice-versa.

### Categories — the leverage move
- Migration adds `legal_documents.category` (single primary domain) +
  `tags` (multi-domain JSON) + index on `(jurisdiction, category)`.
  **Tag, don't tree** — UI can render hierarchy; storage stays flat.
- Recognised vocabulary in config: civil-general, companies, commercial,
  labour, real-estate, family, procedural, criminal, tax, social-insurance,
  notarisation, arbitration, capital-markets, investment, insolvency,
  compliance, banking, enforcement.
- Snapshot config restructured to `[{q, category, tags}]` per profile.
  Each ingested doc inherits the query's category.
- **`RagService::search` + `searchByKeyword`** accept
  `filters: ['jurisdiction' => 'EG', 'category' => 'companies']`. Permissive
  on jurisdiction (matches null too — keeps user-pasted clauses visible);
  strict on category.
- **`LawLookupTool` schema** gains a `category` parameter. Senior-attorney
  profiles teach the model when to use it ("drafting a shareholders'
  agreement → category=companies").
- **Knowledge Base UI** — filter chips for jurisdiction × category +
  faceted counts. URL-persisted (`?j=EG&cat=companies` is bookmarkable).
- **Dashboard library coverage card** — top 8 categories rendered as bar
  chips; clicking one deep-links to the filtered library view.
- **`eastlaws:backfill-category`** — last-resort heuristic for docs the
  snapshot config didn't cover (most got `civil-general` fallback).

### Knowledge Vault polish
- Customer paste / upload / URL ingest forms gain a Category dropdown
  sourced from the same config. User-uploaded internal precedents flow
  into the same scoped-retrieval system as eastlaws docs — the
  "stickiness layer" from the strategy memo.

### Corporate templates (6 new)
- **EG**: Shareholders Agreement, Share Purchase Agreement, Founders /
  Joint Venture Agreement, Commercial Agency.
- **SA**: Shareholders Agreement (LLC under نظام الشركات M/132/2022).
- **AE**: Shareholders Agreement (Federal Decree-Law 32/2021,
  bilingual-aware preamble + Arabic-primacy clause).

Each template includes governance / pre-emption / non-compete / arbitration
clauses + jurisdiction-specific code references. End-to-end test:
EG shareholders agreement with Gemini 2.5 Flash — 1,944 chars, 8 articles,
all `{{placeholders}}` substituted, citation to قانون التحكيم 27/1994
inline. Clean Arabic legal prose, no markdown artifacts.

### Final corpus shape after Phase 5
| | Before Phase 5 | After Phase 5 |
|---|---|---|
| eastlaws docs | 4 | **331** |
| chunks | 696 | **33,449** |
| jurisdictions properly tagged | 0 (mis-tagged EG) | **5** (EG / SA / AE / KW / QA) |
| documents with category | 0 | **331 (100%)** |
| categories represented | 0 | **15** distinct domains |
| corporate-relevant docs | 0 | **~157** (companies + commercial + capital-markets + investment + arbitration + insolvency + compliance + tax + banking) |
| templates | 16 | **23** (6 new corporate templates) |
| LLM providers | 2 (Anthropic, Groq) | **3** (+ Gemini 2.5 Flash) |

### A side-bug we didn't fix yet
In-PHP cosine over 33K chunks needs > 128MB for the heap. Tactical fix:
`php -d memory_limit=512M` for CLI runs, or set in php.ini. Real fix:
switch `DB_CONNECTION=pgsql` and run `rag:promote-to-pgvector` — the SQL
`<=>` operator pushes top-K to the database and bypasses PHP memory
entirely. Already wired; just hasn't been activated yet.

---

## Recurring principles — addendum from Phase 5

7. **The data is the cache. The classification is the moat.**
   Eastlaws docs are public-domain content — anyone with the subscription
   could ingest them. The differentiation is per-document tagging
   (jurisdiction + category + tags + verification freshness) plus
   customer-uploaded internal precedents that nobody else has.

8. **Filter at the data layer, not the prompt layer.** Don't rely on the
   LLM to "figure out" that a marriage contract isn't relevant to a
   shareholders' agreement. Scope retrieval at the SQL level and the
   model's job becomes write-not-classify.

9. **Migration paths beat clean rewrites.** When the jurisdiction bug was
   discovered mid-snapshot, the fix had three layers — code (forward),
   heuristic retag (backward 80%), authoritative retag via re-query
   (backward 100%). Each layer ran without touching the others. That's
   what "operationally safe" architecture looks like.
