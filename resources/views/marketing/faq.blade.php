<?php

$faqs = [
    [
        'q' => __('What is My-lawyer?'),
        'a' => __('My-lawyer is an AI-powered contract drafting platform built for corporate counsel across MENA jurisdictions. It drafts contracts in Arabic, grounds every clause in a curated legal corpus, and exports a bilingual Arabic / English Word document with the language-prevailing clause auto-appended.'),
    ],
    [
        'q' => __('Which jurisdictions does My-lawyer cover?'),
        'a' => __('Eleven Arabic-language jurisdictions: Egypt, Saudi Arabia, the UAE, Kuwait, Qatar, Bahrain, Oman, Jordan, Lebanon, Tunisia, and Libya. Each jurisdiction has its own indexed legal corpus and a curated bilingual translation glossary.'),
    ],
    [
        'q' => __('Does the AI invent statute references?'),
        'a' => __('No. The drafter retrieves authority from the indexed corpus before drafting and refuses to proceed if it cannot find sufficient grounding. Every clause shows its citation, and the citation audit panel marks each as verified, uncertain, or unverified before you finalize.'),
    ],
    [
        'q' => __('How does the bilingual translation work?'),
        'a' => __('The Arabic version is the legally binding text. A separate translation pass produces the English column using a glossary lock (180+ Egyptian civil-law terms, with smaller curated sets per jurisdiction) so terms of art map to their accepted English equivalent. Statute citations are preserved verbatim with parenthetical English.'),
    ],
    [
        'q' => __('Why is the Arabic the legally binding version?'),
        'a' => __('In the MENA region, contracts are filed, registered, notarized, and litigated in Arabic. The English column is a working translation prepared for non-Arabic-reading parties. The output Word file includes a language-prevailing clause stating the Arabic controls in case of discrepancy.'),
    ],
    [
        'q' => __('What if the legal database has stale text?'),
        'a' => __('Indexed documents auto-check against the upstream legal database on a nightly schedule. Stale documents are flagged, re-fetched, and the drafter switches to the new version. Citation audit also surfaces uncertain references before finalization.'),
    ],
    [
        'q' => __('Can I see version history of a contract?'),
        'a' => __('Yes. Every save snapshots the previous body. The History panel shows up to ten prior versions with paragraph-level diff (additions in green, removals in red) so you can see exactly what changed between any two saves.'),
    ],
    [
        'q' => __('Does My-lawyer work offline or self-hosted?'),
        'a' => __('My-lawyer uses cloud LLM providers (Anthropic, Gemini, Groq) for drafting and translation. The platform itself can run on your own infrastructure if needed; contact us for self-hosted licensing.'),
    ],
    [
        'q' => __('What happens if a model provider has an outage?'),
        'a' => __('My-lawyer wraps providers in a multi-key, multi-provider failover chain. When the primary provider rate-limits or fails, the chain automatically routes the request to the next configured provider so your drafting flow does not block.'),
    ],
    [
        'q' => __('Is there a free tier?'),
        'a' => __('First demo is free, no card required. Pricing is per-firm; contact us for the full pricing structure.'),
    ],
];

$locale = app()->getLocale();
$isAr = $locale === 'ar';
$canonical = url('/faq');
$pageTitle = $isAr
    ? 'الأسئلة الشائعة · My-lawyer · صياغة قانونية بالذكاء الاصطناعي للشرق الأوسط'
    : 'FAQ · My-lawyer · AI legal drafting for MENA';
$pageDescription = $isAr
    ? 'أسئلة شائعة عن My-lawyer: الولايات المشمولة، الاستشهادات الموثّقة، الترجمة ثنائية اللغة، سجل الإصدارات، وتعدد مزوّدي الذكاء الاصطناعي.'
    : 'Frequently asked questions about My-lawyer: jurisdictions covered, citation grounding, bilingual translation, version history, and provider failover.';

$jsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(fn ($f) => [
        '@type' => 'Question',
        'name' => $f['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
    ], $faqs),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>

<x-marketing.layout
    :page-title="$pageTitle"
    :page-description="$pageDescription"
    :canonical="$canonical"
    :json-ld="$jsonLd"
>

<style>
    .faq-item {
        border-bottom: 1px solid var(--hairline);
        padding: 1.4rem 0;
    }
    .faq-item summary {
        cursor: pointer;
        list-style: none;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }
    .faq-item summary::-webkit-details-marker { display: none; }
    .faq-q {
        font-family: var(--f-serif);
        font-size: clamp(17px, 1.8vw, 20px);
        font-weight: 500;
        color: var(--ink);
        line-height: 1.4;
        letter-spacing: -0.012em;
        margin: 0;
        flex: 1;
    }
    :where(html[dir="rtl"]) .faq-q { font-family: var(--f-arabic); font-weight: 700; }
    .faq-toggle {
        margin-top: 4px;
        flex-shrink: 0;
        width: 28px; height: 28px;
        border-radius: 50%;
        border: 1px solid var(--hairline);
        color: var(--ink-mute);
        background: var(--canvas);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 14px;
        transition: transform 0.2s ease, background 0.15s, color 0.15s, border-color 0.15s;
    }
    .faq-item[open] .faq-toggle {
        transform: rotate(45deg);
        background: var(--navy); color: #fff;
        border-color: var(--navy);
    }
    .faq-a {
        margin-top: 1rem;
        font-size: 14.5px;
        line-height: 1.7;
        color: var(--ink-soft);
        max-width: 56em;
    }
    :where(html[dir="rtl"]) .faq-a { font-size: 15.5px; line-height: 1.85; }
</style>

{{-- Hero --}}
<section class="section">
    <div class="wrap-narrow">
        <span class="badge-tag" style="margin-bottom: 1.25rem;">
            <span class="pip"></span>{{ __('FAQ') }}
        </span>
        <h1 class="display-serif" style="font-size: clamp(36px, 5.4vw, 60px); margin: 1rem 0 1.5rem;">
            @if ($isAr)
                أسئلة شائعة، <span style="color: var(--navy);">إجابات مباشرة</span>.
            @else
                Common questions, <span style="color: var(--navy);">direct answers</span>.
            @endif
        </h1>
        <p class="lede">
            {{ __('What lawyers ask before they sign up. If your question isn\'t here, the answer is probably "yes, we built that".') }}
        </p>
    </div>
</section>

{{-- FAQ accordion --}}
<section style="padding-bottom: clamp(3.5rem, 6vw, 5rem);">
    <div class="wrap-narrow">
        @foreach ($faqs as $faq)
            <details class="faq-item">
                <summary>
                    <p class="faq-q">{{ $faq['q'] }}</p>
                    <span class="faq-toggle" aria-hidden="true">+</span>
                </summary>
                <div class="faq-a">{{ $faq['a'] }}</div>
            </details>
        @endforeach
    </div>
</section>

{{-- Pull quote --}}
<section style="padding-bottom: clamp(3rem, 5vw, 4.5rem);">
    <div class="wrap">
        <div class="quote-card">
            <span class="qmark" aria-hidden="true">"</span>
            <div>
                <h3 style="font-family: var(--f-serif); font-size: 21px; font-weight: 600; letter-spacing: -0.015em; margin: 0 0 0.85rem; color: var(--ink);">{{ __('The fastest answer is to draft a sample.') }}</h3>
                <p style="margin: 0; font-size: 16px; line-height: 1.7; color: var(--ink); max-width: 56em;">
                    {{ __('Pick a template, describe a deal in Arabic, and see the citations land in 60 seconds. That tells you more about what My-lawyer does than any FAQ entry.') }}
                </p>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="section" style="text-align: center; border-top: 1px solid var(--hairline);">
    <div class="wrap-narrow">
        <h2 class="display-serif" style="font-size: clamp(28px, 4vw, 44px); margin: 0;">
            {{ __('Still have questions?') }}
        </h2>
        <p class="lede" style="margin: 1.25rem auto 2rem;">
            {{ __('Try a sample draft, or reach out — we read every email.') }}
        </p>
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.85rem;">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">{{ __('Open dashboard') }}</a>
            @endauth
            @guest
                <a href="{{ route('register') }}" class="btn-primary">{{ __('Start drafting free') }}</a>
                <a href="{{ route('home') }}#preview" class="btn-outline">{{ __('See sample output') }}</a>
            @endguest
        </div>
    </div>
</section>

</x-marketing.layout>
