<?php
$cfg = config('contract_seo');
$t = $cfg['types'][$type] ?? null;
$j = $cfg['jurisdictions'][$iso] ?? null;
abort_if($t === null || $j === null, 404);

$sample = $cfg['samples'][$type][$iso] ?? null;
$jurs = $cfg['jurisdictions'];

$locale = app()->getLocale();
$isAr = $locale === 'ar';
$canonical = url("/contracts/{$type}/{$iso}");

$pageTitle = $isAr
    ? "صياغة {$t['name_ar']} وفق قانون {$j['ar']} · ثنائية اللغة · My-lawyer"
    : "{$t['name_en']} under {$j['short_en']} law · Bilingual template · My-lawyer";
$pageDescription = $isAr
    ? "قالب {$t['name_ar']} ({$t['short_en']}) ثنائي اللغة عربي/إنجليزي تحت قانون {$j['ar']}. مرتبط بـ{$j['code_ar']}. توثيق الاستشهادات وقفل المصطلحات."
    : "Bilingual {$t['name_en']} ({$t['short_en']}) template under {$j['en']} law. Grounded in {$j['code_en']}. Citation-verified, glossary-locked.";

$jsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            'name' => "{$t['name_en']} drafting under {$j['en']} law",
            'serviceType' => 'AI legal drafting',
            'provider' => ['@type' => 'Organization', 'name' => 'My-lawyer'],
            'areaServed' => ['@type' => 'Country', 'name' => $j['en']],
            'description' => $pageDescription,
            'inLanguage' => ['ar', 'en'],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Contracts', 'item' => url('/contracts')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $t['name_en'], 'item' => url("/contracts/{$type}")],
                ['@type' => 'ListItem', 'position' => 4, 'name' => "{$t['name_en']} — {$j['en']}", 'item' => $canonical],
            ],
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => "What law governs a {$t['name_en']} in {$j['en']}?",
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => "A {$t['name_en']} in {$j['en']} is primarily governed by the {$j['code_en']}. The Arabic text is the legally binding version; the English column is a working translation."],
                ],
                [
                    '@type' => 'Question',
                    'name' => "Which articles does My-lawyer cite for a {$t['short_en']} under {$j['short_en']} law?",
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $sample ? ('Controlling articles include: '.implode(', ', $sample['articles']).'.') : 'Controlling articles vary by clause; My-lawyer cites the specific articles relevant to each clause and verifies them against the live snapshot of the source statute.'],
                ],
            ],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$lastUpdated = '2026-05-10';
$relatedJurs = collect($jurs)->except($iso)->take(6);
?>

<x-marketing.layout
    :page-title="$pageTitle"
    :page-description="$pageDescription"
    :canonical="$canonical"
    :json-ld="$jsonLd"
>

{{-- Breadcrumb --}}
<div class="wrap" style="padding-top: 1.25rem;">
    <nav class="mono" style="font-size: 11px; color: var(--ink-mute); display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('home') }}" class="nav-link" style="padding: 0;">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('marketing.contracts') }}" class="nav-link" style="padding: 0;">{{ __('Contracts') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('marketing.contract-type', ['type' => $type]) }}" class="nav-link" style="padding: 0;">{{ $t['short_en'] }}</a>
        <span aria-hidden="true">›</span>
        <span style="color: var(--ink);">{{ $j['flag'] }} {{ $j['short_en'] }}</span>
    </nav>
</div>

{{-- Hero --}}
<section class="section" style="padding-top: 2rem;">
    <div class="wrap">
        <span class="badge-tag" style="margin-bottom: 1.25rem;">
            <span class="pip"></span>{{ $t['short_en'] }} · {{ $j['short_en'] }}
        </span>
        <h1 class="display-serif" style="font-size: clamp(34px, 5vw, 56px); margin: 1rem 0 1.5rem; max-width: 22ch;">
            <span style="font-size: 0.95em; margin-inline-end: 0.25em; vertical-align: -0.05em;">{{ $j['flag'] }}</span>
            @if ($isAr)
                {{ $t['name_ar'] }} وفق قانون <span style="color: var(--navy);">{{ $j['ar'] }}</span>.
            @else
                {{ $t['name_en'] }} under <span style="color: var(--navy);">{{ $j['en'] }}</span> law.
            @endif
        </h1>
        <p class="lede">
            @if ($isAr)
                صياغة {{ $t['name_ar'] }} ثنائية اللغة، مرتبطة بنصوص {{ $j['code_ar'] }}. كل بند موثَّق بمادته القانونية، والمصطلحات القانونية مقفلة عبر كامل المستند.
            @else
                Bilingual Arabic / English {{ $t['name_en'] }} drafting grounded in {{ $j['code_en'] }}. Every clause cites its controlling article. Civil-law terminology is locked across the document.
            @endif
        </p>
        <div style="display: flex; flex-wrap: wrap; gap: 0.85rem; margin-top: 2rem;">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">{{ __('Open dashboard') }}</a>
            @endauth
            @guest
                <a href="{{ route('register') }}" class="btn-primary">
                    {{ __('Draft this contract') }}
                    <span aria-hidden="true">{{ $isAr ? '←' : '→' }}</span>
                </a>
                <a href="{{ route('marketing.jurisdiction', ['iso' => $iso]) }}" class="btn-outline">
                    {{ __('All :country contracts', ['country' => $isAr ? $j['ar'] : $j['short_en']]) }}
                </a>
            @endguest
        </div>
        <p class="mono" style="margin-top: 1.5rem; font-size: 11.5px; color: var(--ink-mute);">
            {{ __('Last updated') }}: {{ $lastUpdated }}
            @if ($sample)
                · {{ __('Cites') }}: {{ implode(', ', $sample['articles']) }}
            @endif
        </p>
    </div>
</section>

{{-- Sample bilingual clause --}}
@if ($sample)
<section style="padding: 0 0 clamp(3rem, 5vw, 4.5rem);">
    <div class="wrap">
        <div class="biling-card" style="display: grid; grid-template-columns: 1fr; border-radius: 16px; overflow: hidden; background: var(--canvas); border: 1px solid var(--hairline);">
            <style>
                @media (min-width: 880px) { .biling-card { grid-template-columns: 1fr 1fr !important; } }
            </style>
            <div style="padding: 2.25rem 2rem; border-bottom: 1px solid var(--hairline-soft);">
                <span class="eyebrow"><span style="display:inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--navy);"></span>{{ __('Sample clause') }} · العربية</span>
                <p class="ar" dir="rtl" style="margin: 1rem 0 0; font-family: var(--f-arabic); font-size: 16.5px; line-height: 2; font-weight: 400; color: var(--ink);">
                    {!! $sample['clause_ar'] !!}
                </p>
            </div>
            <div style="padding: 2.25rem 2rem; background: var(--surface);">
                <span class="eyebrow"><span style="display:inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--navy);"></span>{{ __('Sample clause') }} · English</span>
                <p style="margin: 1rem 0 0; font-size: 14.5px; line-height: 1.75; color: var(--ink);">
                    {!! $sample['clause_en'] !!}
                </p>
                <div style="margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--hairline-soft); font-family: var(--f-mono); font-size: 11.5px; color: var(--ink-mute);">
                    {{ __('Citations') }}: {{ implode(', ', $sample['articles']) }} — {{ $j['code_en'] }}
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- Key clauses --}}
<section class="section section-surface">
    <div class="wrap">
        <div class="head-block" style="margin-bottom: 2.5rem;">
            <span class="eyebrow"><span style="display:inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--navy);"></span>{{ __('Structure') }}</span>
            <h2>{{ __('What a :type under :country law contains', ['type' => $isAr ? $t['name_ar'] : $t['short_en'], 'country' => $isAr ? $j['ar'] : $j['short_en']]) }}</h2>
            <p class="lede">{{ __('Standard clause structure for this contract type, adjusted for the :country legal tradition.', ['country' => $isAr ? $j['ar'] : $j['short_en']]) }}</p>
        </div>
        <div style="display: grid; gap: 0.85rem; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
            @foreach ($t['key_clauses'] as $i => $clause)
                <div class="card-base" style="padding: 1.1rem 1.25rem; background: var(--canvas);">
                    <div style="display: flex; align-items: baseline; gap: 0.6rem; margin-bottom: 0.3rem;">
                        <span class="mono" style="font-size: 11px; font-weight: 600; color: var(--navy);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 style="font-family: var(--f-sans); font-size: 14px; font-weight: 600; color: var(--ink); margin: 0; letter-spacing: -0.005em;">
                            {{ $isAr ? $clause['ar'] : $clause['en'] }}
                        </h3>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ block (also schemed) --}}
<section class="section">
    <div class="wrap-narrow">
        <div class="head-block" style="margin-bottom: 2rem;">
            <span class="eyebrow"><span style="display:inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--navy);"></span>{{ __('Common questions') }}</span>
            <h2>{{ __('Drafting :type for :country', ['type' => $isAr ? $t['name_ar'] : $t['short_en'], 'country' => $isAr ? $j['ar'] : $j['en']]) }}</h2>
        </div>
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <details style="border-bottom: 1px solid var(--hairline); padding-bottom: 1.25rem;">
                <summary style="cursor: pointer; font-family: var(--f-serif); font-size: 17px; font-weight: 500; color: var(--ink); list-style: none;">
                    {{ __('What law governs a :type in :country?', ['type' => $t['short_en'], 'country' => $isAr ? $j['ar'] : $j['en']]) }}
                </summary>
                <p style="margin: 0.85rem 0 0; font-size: 14.5px; line-height: 1.7; color: var(--ink-soft);">
                    @if ($isAr)
                        تخضع هذه الاتفاقية أساساً لأحكام {{ $j['code_ar'] }}، إلى جانب القوانين الخاصة (الشركات، العمل، الوكالة التجارية) حسب موضوع العقد. النسخة العربية ملزمة قانونياً، والإنجليزية ترجمة عمل.
                    @else
                        This Agreement is primarily governed by the {{ $j['code_en'] }}, together with sector-specific laws (companies, labour, commercial agency) depending on subject matter. The Arabic version is legally binding; the English column is a working translation.
                    @endif
                </p>
            </details>
            @if ($sample)
            <details style="border-bottom: 1px solid var(--hairline); padding-bottom: 1.25rem;">
                <summary style="cursor: pointer; font-family: var(--f-serif); font-size: 17px; font-weight: 500; color: var(--ink); list-style: none;">
                    {{ __('Which articles does My-lawyer cite?') }}
                </summary>
                <p style="margin: 0.85rem 0 0; font-size: 14.5px; line-height: 1.7; color: var(--ink-soft);">
                    {{ __('Controlling articles for this contract type include') }}:
                    <span class="mono" style="font-size: 13px; color: var(--ink);">{{ implode(', ', $sample['articles']) }}</span>.
                    {{ __('My-lawyer cites the specific articles relevant to each clause and verifies them against the live snapshot of the source statute.') }}
                </p>
            </details>
            @endif
            <details style="border-bottom: 1px solid var(--hairline); padding-bottom: 1.25rem;">
                <summary style="cursor: pointer; font-family: var(--f-serif); font-size: 17px; font-weight: 500; color: var(--ink); list-style: none;">
                    {{ __('Is the Arabic or English version legally binding?') }}
                </summary>
                <p style="margin: 0.85rem 0 0; font-size: 14.5px; line-height: 1.7; color: var(--ink-soft);">
                    @if ($isAr)
                        في {{ $j['ar'] }}، النسخة العربية هي الملزمة قانونياً للتسجيل والإيداع والتقاضي. النسخة الإنجليزية ترجمة عمل، وعلى المستند بند يوضح أن العربية هي السائدة في حال الاختلاف.
                    @else
                        In {{ $j['en'] }}, the Arabic version is legally binding for registration, filing, and litigation. The English version is a working translation. The output document includes a language-prevailing clause stating Arabic controls in case of discrepancy.
                    @endif
                </p>
            </details>
        </div>
    </div>
</section>

{{-- Related: same type, other jurisdictions --}}
<section class="section" style="background: var(--surface); border-top: 1px solid var(--hairline);">
    <div class="wrap">
        <div class="head-block" style="margin-bottom: 2rem;">
            <span class="eyebrow"><span style="display:inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--navy);"></span>{{ __('Same contract, other jurisdictions') }}</span>
            <h2 style="font-size: clamp(24px, 3vw, 32px);">{{ __('Compare :type across the region', ['type' => $isAr ? $t['name_ar'] : $t['short_en']]) }}</h2>
        </div>
        <div style="display: grid; gap: 0.85rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            @foreach ($relatedJurs as $rIso => $rJ)
                <a href="{{ route('marketing.contract-jurisdiction', ['type' => $type, 'iso' => $rIso]) }}" class="jur-card">
                    <div class="jur-head">
                        <span class="flag" aria-hidden="true">{{ $rJ['flag'] }}</span>
                        <span class="nm {{ $isAr ? 'ar' : '' }}">{{ $isAr ? $rJ['ar'] : $rJ['en'] }}</span>
                        <span class="iso">{{ $rIso }}</span>
                    </div>
                    <p class="ref-line">{{ $rJ['code_en'] }}</p>
                </a>
            @endforeach
        </div>
        <p style="margin-top: 1.5rem;">
            <a href="{{ route('marketing.contract-type', ['type' => $type]) }}" class="btn-link">
                {{ __('See all :n jurisdictions', ['n' => count($jurs)]) }}
                <span aria-hidden="true">{{ $isAr ? '←' : '→' }}</span>
            </a>
        </p>
    </div>
</section>

{{-- CTA --}}
<section class="section" style="text-align: center; border-top: 1px solid var(--hairline);">
    <div class="wrap-narrow">
        <h2 class="display-serif" style="font-size: clamp(28px, 4vw, 44px); margin: 0;">
            {{ __('Draft your :type now.', ['type' => $isAr ? $t['name_ar'] : $t['short_en']]) }}
        </h2>
        <p class="lede" style="margin: 1.25rem auto 2rem;">
            {{ __('Describe the deal in Arabic or English and get a bilingual draft with verified citations to :code in 60 seconds.', ['code' => $isAr ? $j['code_ar'] : $j['code_en']]) }}
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
