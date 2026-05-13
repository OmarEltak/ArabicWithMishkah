# My-lawyer Production Deployment Checklist

> Read top-to-bottom for a first deploy. Steps you do yourself (account
> creation, DNS, secrets) are marked `[YOU]`. Steps an engineer wires after
> you provide credentials are marked `[ENG]`. Everything is in order — don't
> skip ahead.

---

## 0. Pre-flight (1 hour)

- [ ] **[YOU]** Pick a domain. Recommendation: `my-lawyer.com` if available,
      else `my-lawyer.app` or `my-lawyer.io`. Avoid `.test` (reserved),
      `.dev` (Google-controlled, requires HTTPS at registry), or vanity TLDs
      like `.law` (expensive, weak SEO signal vs. `.com`).
- [ ] **[YOU]** Register through Cloudflare Registrar (cheapest, ~$10/yr,
      WHOIS privacy free). Or Namecheap if you want familiar UX.
- [ ] **[YOU]** Decide hosting:
      - **Laravel Forge + DigitalOcean** — simplest, $5–20/mo droplet,
        Forge handles deploys, SSL, queue workers. **Recommended for
        first deploy.**
      - **Laravel Vapor + AWS** — serverless, scales to zero, costs more
        per active hour but $0 idle. Pick this if traffic is bursty.
      - **Railway / Render** — single-click deploy, opinionated. Good for
        a 1-engineer team that doesn't want to learn DevOps.
- [ ] **[YOU]** Decide email provider:
      - **Postmark** — transactional only, best deliverability, $15/mo.
      - **Mailgun** — flexible, marketing + transactional, $35/mo and up.
      - Pick Postmark for the first deploy; add Mailgun later if marketing
        emails grow.

---

## 1. Database (Postgres + pgvector)

The local SQLite DB does NOT carry over to production. Postgres is required
for pgvector (real semantic search) and for concurrent queue workers.

- [ ] **[YOU]** Create a managed Postgres instance:
      - **DigitalOcean Managed Database** ($15/mo, easy)
      - **Supabase** (already in your stack — see existing Supabase
        projects in the codebase context)
      - **AWS RDS** if you went Vapor
- [ ] **[YOU]** Enable the `pgvector` extension on the new DB:
      ```sql
      CREATE EXTENSION IF NOT EXISTS vector;
      ```
- [ ] **[ENG]** Set `DB_CONNECTION=pgsql` + connection vars in production
      `.env`.
- [ ] **[ENG]** Run `php artisan migrate --force` on the new DB.
- [ ] **[ENG]** Seed the corpus: `php artisan legal:seed-catalog`.
- [ ] **[ENG]** Backfill embeddings: `php artisan legal:embed-backfill`.
      (See section 4 — needs OPENAI_API_KEY or VOYAGE_API_KEY set first
      for real vectors; runs with mock fallback otherwise.)
- [ ] **[ENG]** Set up daily backup + 7-day point-in-time recovery.

---

## 2. Redis + Queue Worker

The drafting flow uses queued jobs (freshness checks, deferred ingestion).
Sync queue won't survive request timeouts on long LLM calls.

- [ ] **[YOU]** Spin up Redis: DigitalOcean Managed Redis ($10/mo) or
      Upstash (serverless, pay-per-request).
- [ ] **[ENG]** Set `QUEUE_CONNECTION=redis` + `REDIS_*` vars.
- [ ] **[ENG]** Run `php artisan queue:work redis --tries=3 --timeout=300`
      under a supervisor process (Forge: built-in "Daemons" feature; Vapor:
      built into worker tier).
- [ ] **[ENG]** Verify with `php artisan health:report` that
      `queue.status: healthy`.

---

## 3. LLM Providers (the cost driver)

The drafting service uses a multi-provider failover chain. At minimum you
need **one** real provider key; ideally two (primary + fallback).

- [ ] **[YOU]** Anthropic — primary for Arabic legal drafting:
      [https://console.anthropic.com/settings/keys]. Set
      `ANTHROPIC_API_KEY` and pick the Claude Opus 4.7 model in
      `.env`: `LAWYER_PROVIDERS_PRIMARY=anthropic`.
- [ ] **[YOU]** Google Gemini — fallback:
      [https://aistudio.google.com/api-keys]. Set `GOOGLE_GEMINI_API_KEY`.
- [ ] **[YOU]** (Optional) Groq — fastest tertiary fallback for clarifying
      questions: [https://console.groq.com/keys]. Set `GROQ_API_KEY`.
- [ ] **[ENG]** Verify chain via `php artisan health:report` —
      `providers.count` should match the number of keys set.

### Cost budget per draft

| Plan | Avg tokens / draft | Cost / draft (Claude Opus) | Cost / month at plan cap |
|---|---|---|---|
| Free | ~12k | $0.18 | $0.54 (3 drafts) |
| Solo | ~12k | $0.18 | $4.50 (25 drafts) |
| Firm | ~12k | $0.18 | $36 (200 drafts) |
| Enterprise | ~12k | $0.18 | unlimited |

These are upper bounds — clarifying turns + tool calls add ~30% overhead.
Budget 1.5× the above for headroom.

---

## 4. Embeddings (RAG quality)

Currently runs on mock hash embeddings. Production-quality retrieval needs
a real embedding provider.

- [ ] **[YOU]** Pick one:
      - **OpenAI** `text-embedding-3-large` (3072-d, $0.13/M tokens) —
        best quality, costs ~$15 to embed the full 87k chunks once.
      - **Voyage AI** `voyage-3` (1024-d, $0.06/M tokens) — half the cost,
        marginally lower quality for legal Arabic. **Recommended for
        first deploy.**
- [ ] **[YOU]** Set `OPENAI_API_KEY` or `VOYAGE_API_KEY` in `.env`.
- [ ] **[ENG]** Re-embed the corpus:
      `php artisan legal:embed-backfill --force`.
      One-shot cost: $5–$15. Takes ~10 minutes.
- [ ] **[ENG]** Verify: `php artisan tinker` →
      `App\Services\AI\EmbeddingService::fromConfig()->isConfigured()` →
      should return `true`.

---

## 5. Audit Chain + Health Endpoint

- [ ] **[ENG]** Generate the audit-log signing key:
      ```bash
      php -r "echo base64_encode(random_bytes(48));"
      ```
      Set as `APP_AUDIT_KEY` in production `.env`. **Different from
      `APP_KEY`. Once set, never rotate without forfeiting old audit
      rows.**
- [ ] **[ENG]** Generate the internal-health-endpoint key:
      ```bash
      php -r "echo bin2hex(random_bytes(32));"
      ```
      Set as `APP_INTERNAL_HEALTH_KEY`.
- [ ] **[ENG]** Verify chain: `php artisan audit:verify` should return
      "✓ Chain valid."

---

## 6. Email Provider

- [ ] **[YOU]** Sign up for Postmark. Verify your sender domain (DKIM +
      Return-Path DNS records — Postmark gives you the exact records).
- [ ] **[YOU]** Add SPF record to your domain:
      `v=spf1 include:spf.mtasv.net ~all`
- [ ] **[ENG]** Set Postmark vars:
      ```
      MAIL_MAILER=postmark
      POSTMARK_TOKEN=...
      MAIL_FROM_ADDRESS=hello@my-lawyer.com
      MAIL_FROM_NAME=My-lawyer
      ```
- [ ] **[ENG]** Send a test:
      `php artisan tinker` → `Mail::raw('test', fn($m) => $m->to('your@email')->subject('Postmark test'));`

---

## 7. Error Monitoring

Already half-wired — `errors` log channel exists. Sentry is the easiest
final step.

- [ ] **[YOU]** Create a Sentry account + Laravel project at
      [https://sentry.io]. Copy the DSN.
- [ ] **[ENG]** Install: `composer require sentry/sentry-laravel`.
- [ ] **[ENG]** Set `SENTRY_LARAVEL_DSN=...` in `.env`.
- [ ] **[ENG]** Test: throw a deliberate exception from a dev route,
      verify it lands in the Sentry dashboard.

Alternative without Sentry: poll `storage/logs/errors.log` via
[Better Stack](https://betterstack.com) ($25/mo) or
[Papertrail](https://papertrailapp.com) ($7/mo).

---

## 8. Uptime Monitoring

- [ ] **[YOU]** Sign up for Better Stack Uptime (free tier covers 10
      monitors).
- [ ] **[YOU]** Add monitor: `GET https://my-lawyer.com/__internal/health`
      with header `X-Internal-Health-Key: <value from step 5>`,
      every 1 minute, alert if status is not `healthy`.
- [ ] **[YOU]** Add Slack / email webhook for alert delivery.

---

## 9. Domain + DNS + SSL

- [ ] **[YOU]** Point the domain at your host:
      - Forge: A record → droplet IP
      - Vapor: CNAME → vapor-provided hostname
      - Railway / Render: CNAME → service-provided hostname
- [ ] **[YOU]** Issue SSL certificate (Forge / Vapor / Railway do this
      automatically via Let's Encrypt).
- [ ] **[ENG]** Set `APP_URL=https://my-lawyer.com` in production `.env`.
- [ ] **[ENG]** Verify HTTPS works end-to-end:
      `curl -I https://my-lawyer.com/` → 200.

---

## 10. CDN + Static Assets

- [ ] **[YOU]** (Optional) Front the site with Cloudflare Free tier.
      Pros: free DDoS protection, edge cache, faster TTFB for far users.
      Cons: extra hop, occasional cache-invalidation friction.
- [ ] **[ENG]** Build assets in production: `npm ci && npm run build`.
- [ ] **[ENG]** Verify Vite manifests + that fonts load over HTTPS.

---

## 11. Scheduled Tasks (Freshness Worker)

- [ ] **[ENG]** Add cron entry on the host:
      ```
      * * * * * cd /path/to/app && php artisan schedule:run
      ```
      Laravel's scheduler routes daily / hourly / weekly tasks from
      `routes/console.php`. The freshness checker is already wired there.
- [ ] **[ENG]** After 24 hours, verify
      `php artisan legal:status` → `freshness 100%` (or close).

---

## 12. Pre-launch Checklist

Run through these BEFORE pointing anyone at the URL.

- [ ] All 5 legal pages (`/legal/terms`, `/legal/privacy`, `/legal/dpa`,
      `/legal/aup`) render correctly + are linked from footer.
- [ ] Sitemap renders 120+ URLs at `https://my-lawyer.com/sitemap.xml`.
- [ ] `robots.txt` allows GPTBot, ClaudeBot, PerplexityBot, Google-Extended.
- [ ] `/llms.txt` and `/pricing.md` are reachable.
- [ ] AggregateRating JSON-LD is REMOVED from homepage (we did this in
      Phase 3 — verify it stayed removed after the deploy).
- [ ] `php artisan health:report` returns all-green.
- [ ] `php artisan audit:verify` returns "Chain valid."
- [ ] `php artisan legal:status` shows 11 jurisdictions covered.
- [ ] Create a fresh test account, run through a full draft + edit flow,
      confirm corpus_gate populates + needs_review banner works.
- [ ] All 8 contract types render at `/contracts/{type}` (88 jurisdiction
      pages render at `/contracts/{type}/{iso}`).
- [ ] Voice dictation works on HTTPS (no Chrome flag needed once on real
      SSL).
- [ ] Free-plan draft cap triggers correctly at 3 drafts.

---

## 13. Day-0 Marketing

(These are user actions, not engineering.)

- [ ] Submit sitemap to Google Search Console.
- [ ] Submit sitemap to Bing Webmaster Tools.
- [ ] Submit URL to [IndexNow](https://www.indexnow.org) for instant
      Bing/Yandex indexing.
- [ ] Create the LinkedIn company page + initial post announcing the tool.
- [ ] One Reddit post in r/legaltech or r/sweattech_egypt (DON'T spam —
      one genuine, helpful post tagged with "I made this" framing).
- [ ] First batch of cold-emails to 20 mid-market Cairo law firms (use
      the lawyer-review packet as a credibility lead — "we'd love a 30-min
      review of how this drafts your typical SPA in exchange for X").

---

## Environment Variable Inventory

The complete list of secrets you'll need to provision before the first
deploy.

### Application
```
APP_NAME=My-lawyer
APP_ENV=production
APP_KEY=<artisan key:generate output>
APP_AUDIT_KEY=<base64(48 random bytes)>
APP_INTERNAL_HEALTH_KEY=<32 hex>
APP_URL=https://my-lawyer.com
APP_DEBUG=false
```

### Database
```
DB_CONNECTION=pgsql
DB_HOST=...
DB_PORT=5432
DB_DATABASE=mylawyer
DB_USERNAME=...
DB_PASSWORD=...
```

### Redis / Queue
```
REDIS_HOST=...
REDIS_PASSWORD=...
REDIS_PORT=6379
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### LLM Providers
```
ANTHROPIC_API_KEY=sk-ant-...
GOOGLE_GEMINI_API_KEY=...
GROQ_API_KEY=gsk_...
LAWYER_PROVIDERS_PRIMARY=anthropic
LAWYER_MAX_CLARIFYING_QUESTIONS=3
LAWYER_DRAFT_MAX_TOKENS=8000
```

### Embeddings
```
OPENAI_API_KEY=sk-...
# OR
VOYAGE_API_KEY=pa-...
```

### Email
```
MAIL_MAILER=postmark
POSTMARK_TOKEN=...
MAIL_FROM_ADDRESS=hello@my-lawyer.com
MAIL_FROM_NAME=My-lawyer
```

### Monitoring
```
SENTRY_LARAVEL_DSN=https://...@o0.ingest.sentry.io/0
```

### Eastlaws (existing upstream legal feed)
```
EASTLAWS_API_KEY=...
EASTLAWS_BASE_URL=...
```

---

## What's NOT in this checklist (deliberately)

- **Billing / Stripe / Tap** — deferred to Phase 7. Free-tier hard cap is
  already enforced (3 drafts/month), so abuse is bounded until billing
  ships.
- **CDN tuning beyond Cloudflare Free** — premature optimization for a
  pre-launch product. Revisit once traffic > 10k visits/day.
- **Multi-region failover** — single region (eu-west or me-south) is
  fine for MENA-targeted launch.
- **CI/CD pipeline beyond Forge's built-in auto-deploy** — overkill for
  a small team.

---

## Order of operations on launch day

1. Provision DB, Redis, all secrets — 30 min.
2. Deploy code (Forge auto-deploy from `main`) — 5 min.
3. Run migrations + seeders + embedding backfill — 20 min.
4. Run pre-launch checklist (section 12) — 30 min.
5. Flip DNS to point at the host — 5 min.
6. Smoke test from incognito / different network — 15 min.
7. Submit sitemap to Google/Bing — 5 min.
8. Announce.

**Total: ~2 hours from "go" to "live."** Most variance is in waiting for
DNS propagation. Pad it to a half-day for first deploy.
