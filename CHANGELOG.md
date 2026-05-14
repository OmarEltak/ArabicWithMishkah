# Changelog

Notable user-facing changes shipped to My-lawyer. Newest first.

## 2026-05-14

### Added
- **Counter-proposal flow** — paste a counterparty's redlined contract on the editor; see a live diff against the current version; accept to save a new version with the old one preserved in history.
- **Command palette** — press ⌘K (or Ctrl+K) anywhere in the app to jump to any contract, matter, template, clipping, or law, plus quick actions (new draft, new matter, etc.).
- **Bulk actions on contracts** — multi-select rows to archive, move-to-matter, or export-as-zip in one go.
- **Help Center** — new `/help` with how-to articles on drafting, citations, matters, bilingual export, billing, and the command palette.
- **Translation cache** — re-translating the same contract body to the same language is instant; we cache for 30 days. Saves ~$0.04 and ~10s per repeat export.

## 2026-05-13

### Added
- **Matters / clients hierarchy** — group contracts and chat sessions by client and matter. New `/lawyer/matters` page with create/edit/archive and live search.
- **Clippings** — save excerpts from any law document into a personal research file with notes and tags. Filter and edit from `/lawyer/clippings`.
- **PDF export** — download contracts as `.pdf` alongside `.docx`, single-language or bilingual.
- **Citation copy-to-clipboard** — every article on the law-show page now has a one-click "Copy citation" button.
- **Usage forecast** — the usage page projects your end-of-month spend at the current burn rate and flags days-to-cap when you're trending high.

### Improved
- Templates page now has a mobile drawer (was invisible on phones).
- Contracts list paginated 20/page (was hard-capped at 100).
- New first-run welcome card on the usage page when there's no activity yet.

## 2026-05-12

### Added
- **Stripe billing via Cashier** — Solo and Firm plans live; 14-day free trial; Stripe customer portal for managing payment methods.
- **Branded error pages** — bilingual 401/403/404/419/429/500/503 with a reference ID lawyers can quote to support.
- **Security headers middleware** — X-Frame-Options, X-Content-Type-Options, Referrer-Policy, HSTS over HTTPS.
- **Rate limiting** on AI/chat routes, locale switcher, webhook, and internal health.

### Improved
- Production refuses to fall back to `APP_KEY` for audit-chain signing (now requires a dedicated `APP_AUDIT_KEY`).
- Audit log subject rows link to their underlying contract / document / template.
- Hot-path indexes on `chat_sessions`, `chat_messages`, and `contracts` foreign keys.

## 2026-05-10

### Added
- **Cookie consent banner** under Egypt Personal Data Protection Law 151/2020.
- **Onboarding card** on the dashboard for first-run users.
- **Citation panel visible from lg breakpoint** — was previously hidden until xl, leaving 13" laptop users without it.

## 2026-05-06

### First release scaffolding
- Public marketing surface (home, 11 jurisdiction landing pages, contracts, glossary, FAQ, pricing, legal pages).
- Authenticated lawyer app: dashboard, chat workspace, contracts, templates, knowledge base, law search, law-show, usage, audit.
- AI/RAG stack with Anthropic + Gemini + Groq failover.
- Corpus of 124 statutes across 11 MENA jurisdictions.
- Bilingual (Arabic/English) with RTL support throughout.
- HMAC-chained audit log.
