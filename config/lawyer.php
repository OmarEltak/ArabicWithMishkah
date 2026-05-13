<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Billing — trial period
    |--------------------------------------------------------------------------
    |
    | Days of free trial offered on first checkout for any paid plan.
    | Cashier respects the value (no card needed during trial when set).
    | Set to 0 to require a card up front.
    */

    'trial_days' => (int) env('LAWYER_TRIAL_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | LLM and Embedding Provider
    |--------------------------------------------------------------------------
    */

    'llm_provider' => env('LLM_PROVIDER', 'anthropic'),

    // When true, LlmFactory wraps the primary in LlmFailoverChain so a
    // request that exhausts every key in the primary's pool transparently
    // falls through to the next configured provider (Anthropic → Gemini →
    // Groq, primary always tried first). Set false for evaluation runs
    // where a provider's failure should surface, not be papered over.
    'llm_fallback' => (bool) env('LAWYER_LLM_FALLBACK', true),

    'embedding_provider' => env('EMBEDDING_PROVIDER', 'voyage'),

    /*
    |--------------------------------------------------------------------------
    | RAG Tuning
    |--------------------------------------------------------------------------
    */

    'chunk_size' => (int) env('LAWYER_CHUNK_SIZE', 900),
    'chunk_overlap' => (int) env('LAWYER_CHUNK_OVERLAP', 120),
    'top_k' => (int) env('LAWYER_TOP_K', 6),
    // Max chars per chunk emitted into the prompt's LEGAL_CONTEXT. Prevents
    // tight token budgets (e.g. Groq free tier @ 6K TPM) from overflowing.
    'context_chunk_max_chars' => (int) env('LAWYER_CONTEXT_CHUNK_MAX_CHARS', 500),
    // Per-citation char cap and max-citations cap when LawLookupTool emits
    // results back to the LLM. Tight token budgets benefit from low values.
    'tool_chunk_max_chars' => (int) env('LAWYER_TOOL_CHUNK_MAX_CHARS', 600),
    'tool_max_results' => (int) env('LAWYER_TOOL_MAX_RESULTS', 2),
    // Maximum tool-use rounds before the model must finalise. Each round adds
    // a user/assistant pair to history, so tight TPMs require fewer rounds.
    'max_tool_rounds' => (int) env('LAWYER_MAX_TOOL_ROUNDS', 2),
    // Reserved output tokens for the drafting LLM call. Tight TPM tiers
    // (Groq free = 6K TPM total = input + reserved output) need this low.
    'draft_max_tokens' => (int) env('LAWYER_DRAFT_MAX_TOKENS', 4096),
    // Caps the senior-attorney profile size injected into the drafting
    // system prompt. The profile is rich domain knowledge — generally worth
    // the tokens — but tight token tiers (Groq free) benefit from trimming.
    'attorney_profile_max_chars' => (int) env('LAWYER_ATTORNEY_PROFILE_MAX_CHARS', 8000),

    /*
    |--------------------------------------------------------------------------
    | Drafting Behavior
    |--------------------------------------------------------------------------
    |
    | max_clarifying_questions caps how many rounds the AI is allowed to ask
    | the lawyer before it must produce a draft.
    |
    */

    // Number of clarifying-question rounds the assistant runs before forcing
    // a draft. 3 is a sensible default: the model can still draft on turn 1
    // if it returns ready_to_draft=true, but for short intents it asks for
    // missing parties / dates / governing law first.
    //
    // 0 = skip questions entirely (legacy behavior — never asks, always
    //     drafts immediately on the first message; this was the previous
    //     default and produced the "AI doesn't ask, just dumps a draft" UX).
    'max_clarifying_questions' => (int) env('LAWYER_MAX_CLARIFYING_QUESTIONS', 3),

    /*
    |--------------------------------------------------------------------------
    | Per-Plan Usage Limits
    |--------------------------------------------------------------------------
    |
    | Hard caps on new contract drafts per calendar month, per plan. Free is
    | a tight cap to prevent abuse pre-billing. Enforced in
    | App\Services\Contracts\PlanUsageGate. `null` = unlimited.
    |
    | Edits to existing drafts do NOT count — only fresh drafts that produce
    | a new Contract row.
    |
    */
    'plan_limits' => [
        'free' => ['drafts_per_month' => 3],
        'solo' => ['drafts_per_month' => 25],
        'firm' => ['drafts_per_month' => 200],
        'enterprise' => ['drafts_per_month' => null], // unlimited
    ],

    /*
    |--------------------------------------------------------------------------
    | Plan Catalog (UI + Stripe)
    |--------------------------------------------------------------------------
    |
    | Display metadata for /pricing and /settings/billing, plus the Stripe
    | product/price IDs the checkout flow uses. Set the IDs after creating
    | the products in your Stripe Dashboard — see docs/BILLING.md.
    |
    | "stripe_price_monthly" and "stripe_price_yearly" can be left null in
    | development; the pricing page renders fine, the checkout endpoint
    | returns a 503 with a clear "Stripe not configured" message instead
    | of attempting an API call.
    |
    */
    'plans' => [
        'free' => [
            'name_en' => 'Free',
            'name_ar' => 'مجاني',
            'price_monthly_egp' => 0,
            'price_yearly_egp' => 0,
            'currency' => 'EGP',
            'stripe_price_monthly' => null,
            'stripe_price_yearly' => null,
            'tap_plan_id' => null,
            'features' => [
                'en' => [
                    '3 contract drafts per month',
                    'All 11 MENA jurisdictions',
                    'Bilingual export (watermarked)',
                    'Citation-grounded clauses',
                ],
                'ar' => [
                    '٣ مسودات عقود شهرياً',
                    'جميع ولايات الشرق الأوسط الـ ١١',
                    'تصدير ثنائي اللغة (بعلامة مائية)',
                    'استشهادات قانونية موثَّقة',
                ],
            ],
            'cta_en' => 'Start free',
            'cta_ar' => 'ابدأ مجاناً',
            'highlighted' => false,
        ],
        'solo' => [
            'name_en' => 'Solo',
            'name_ar' => 'فردي',
            'price_monthly_egp' => 2400,    // EGP ~ $50
            'price_yearly_egp' => 24000,
            'currency' => 'EGP',
            'stripe_price_monthly' => env('STRIPE_PRICE_SOLO_MONTHLY'),
            'stripe_price_yearly' => env('STRIPE_PRICE_SOLO_YEARLY'),
            'tap_plan_id' => env('TAP_PLAN_SOLO'),
            'features' => [
                'en' => [
                    '25 contract drafts per month',
                    'Unlimited edits per draft',
                    'No watermark on export',
                    'Full revision history (10 versions per contract)',
                    'Email support',
                ],
                'ar' => [
                    '٢٥ مسودة عقد شهرياً',
                    'تعديلات غير محدودة لكل مسودة',
                    'تصدير بدون علامة مائية',
                    'سجل المراجعات الكامل (١٠ إصدارات لكل عقد)',
                    'دعم بالبريد الإلكتروني',
                ],
            ],
            'cta_en' => 'Start Solo trial',
            'cta_ar' => 'ابدأ فترة تجريبية',
            'highlighted' => true,
        ],
        'firm' => [
            'name_en' => 'Firm',
            'name_ar' => 'شركة',
            'price_monthly_egp' => 7900,    // EGP ~ $165
            'price_yearly_egp' => 79000,
            'currency' => 'EGP',
            'stripe_price_monthly' => env('STRIPE_PRICE_FIRM_MONTHLY'),
            'stripe_price_yearly' => env('STRIPE_PRICE_FIRM_YEARLY'),
            'tap_plan_id' => env('TAP_PLAN_FIRM'),
            'features' => [
                'en' => [
                    '200 contract drafts per month',
                    '5 user seats',
                    'Shared workspace and contract library',
                    'Team-wide glossary lock',
                    'Citation audit dashboard',
                    'Priority support (24h SLA)',
                ],
                'ar' => [
                    '٢٠٠ مسودة عقد شهرياً',
                    '٥ مستخدمين',
                    'مساحة عمل مشتركة ومكتبة عقود',
                    'قفل مصطلحات على مستوى الفريق',
                    'لوحة تدقيق الاستشهادات',
                    'دعم بأولوية (SLA ٢٤ ساعة)',
                ],
            ],
            'cta_en' => 'Start Firm trial',
            'cta_ar' => 'ابدأ فترة تجريبية',
            'highlighted' => false,
        ],
        'enterprise' => [
            'name_en' => 'Enterprise',
            'name_ar' => 'مؤسسات',
            'price_monthly_egp' => null, // contact sales
            'price_yearly_egp' => null,
            'currency' => 'EGP',
            'stripe_price_monthly' => null,
            'stripe_price_yearly' => null,
            'tap_plan_id' => null,
            'features' => [
                'en' => [
                    'Unlimited drafts and users',
                    'Self-hosted deployment option',
                    'SSO (SAML / OIDC)',
                    'Custom glossary onboarding',
                    'Dedicated account manager',
                    'Custom DPA + 99.9% uptime SLA',
                ],
                'ar' => [
                    'مسودات ومستخدمون غير محدودين',
                    'خيار النشر الذاتي الاستضافة',
                    'الدخول الموحَّد (SSO)',
                    'إدراج مصطلحات مخصصة',
                    'مدير حساب مخصص',
                    'اتفاقية معالجة بيانات مخصصة + توافر ٩٩.٩٪',
                ],
            ],
            'cta_en' => 'Contact sales',
            'cta_ar' => 'تواصل مع المبيعات',
            'highlighted' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Disclaimer
    |--------------------------------------------------------------------------
    |
    | Shown on every generated contract. Edit per jurisdiction.
    |
    */

    'disclaimer' => 'AI-generated draft. Must be reviewed and approved by a licensed attorney before use. Not legal advice.',

    /*
    |--------------------------------------------------------------------------
    | Cost Controls
    |--------------------------------------------------------------------------
    |
    | Hard daily caps. When exceeded, AI calls throw a BudgetExceededException
    | which the UI converts into a polite "limit reached, try tomorrow" toast.
    |
    */

    'daily_budget_usd' => (float) env('LAWYER_DAILY_BUDGET_USD', 50.0),
    'per_user_daily_budget_usd' => (float) env('LAWYER_PER_USER_DAILY_BUDGET_USD', 5.0),

    /*
    |--------------------------------------------------------------------------
    | Provider Pricing
    |--------------------------------------------------------------------------
    |
    | USD per 1M tokens. Update from each provider's pricing page. Cost is
    | locked in at usage write-time so historical events stay correct.
    |
    */

    'pricing' => [
        // ── Gemini (primary) ──────────────────────────────────────────────
        // Gemini 2.5 Pro: tiered — first 200k tokens cheaper than the rest.
        // We bake the ≤200k tier here as the planning baseline; the
        // UsageTracker computes the higher tier when a prompt actually
        // crosses 200k input tokens. Drafts rarely cross.
        'gemini.gemini-2.5-pro' => ['input' => 1.25, 'output' => 10.00, 'cache_read' => 0.31],
        'gemini.gemini-2.5-flash' => ['input' => 0.30, 'output' => 2.50],
        'gemini' => ['input' => 1.25, 'output' => 10.00],

        // ── Anthropic (fallback) ──────────────────────────────────────────
        'anthropic.claude-sonnet-4-6' => ['input' => 3.00, 'output' => 15.00, 'cache_read' => 0.30, 'cache_creation' => 3.75],
        'anthropic.claude-opus-4-7' => ['input' => 15.00, 'output' => 75.00, 'cache_read' => 1.50, 'cache_creation' => 18.75],
        'anthropic.claude-haiku-4-5-20251001' => ['input' => 0.80, 'output' => 4.00, 'cache_read' => 0.08, 'cache_creation' => 1.00],
        'anthropic' => ['input' => 3.00, 'output' => 15.00],

        // ── Embeddings ────────────────────────────────────────────────────
        'voyage.voyage-3' => ['input' => 0.06, 'output' => 0.0],
        'voyage' => ['input' => 0.06, 'output' => 0.0],
        'openai.text-embedding-3-small' => ['input' => 0.02, 'output' => 0.0],
        'openai.text-embedding-3-large' => ['input' => 0.13, 'output' => 0.0],
        'openai' => ['input' => 0.02, 'output' => 0.0],
    ],

];
