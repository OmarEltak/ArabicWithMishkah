<?php
$cfg = config('contract_seo');
$t = $cfg['types'][$type] ?? null;
abort_if($t === null, 404);
$jurs = $cfg['jurisdictions'];
$samples = $cfg['samples'][$type] ?? [];

$locale = app()->getLocale();
$isAr = $locale === 'ar';
$canonical = url("/contracts/{$type}");

$pageTitle = $isAr
    ? "صياغة {$t['name_ar']} ثنائية اللغة · ١١ ولاية في الشرق الأوسط · My-lawyer"
    : "{$t['name_en']} ({$t['short_en']}) · Bilingual MENA template · My-lawyer";
$pageDescription = $isAr
    ? "قالب {$t['name_ar']} ثنائي اللغة عربي/إنجليزي، مرتبط بنصوص القانون المدني في كل ولاية قضائية. {$t['tagline_ar']}"
    : "Bilingual Arabic / English {$t['name_en']} template grounded in the civil code of each MENA jurisdiction. {$t['tagline_en']}";

$jsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Service',
            'name' => "{$t['name_en']} drafting (MENA)",
            'serviceType' => 'AI legal drafting',
            'provider' => ['@type' => 'Organization', 'name' => 'My-lawyer'],
            'areaServed' => array_values(array_map(fn ($j) => ['@type' => 'Country', 'name' => $j['en']], $jurs)),
            'description' => $pageDescription,
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Contracts', 'item' => url('/contracts')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $t['name_en'], 'item' => $canonical],
            ],
        ],
        [
            '@type' => 'ItemList',
            'name' => "{$t['name_en']} versions by jurisdiction",
            'numberOfItems' => count($jurs),
            'itemListElement' => array_values(array_map(fn ($iso, $j) => [
                '@type' => 'ListItem',
                'position' => array_search($iso, array_keys($jurs)) + 1,
                'name' => "{$t['name_en']} — {$j['en']}",
                'url' => url("/contracts/{$type}/{$iso}"),
            ], array_keys($jurs), $jurs)),
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$lastUpdated = '2026-05-10';
?>

<x-marketing.layout
    :page-title="$pageTitle"
    :page-description="$pageDescription"
    :canonical="$canonical"
    :json-ld="$jsonLd"
>

{{-- Breadcrumb --}}
<div class="wrap" style="padding-top: 1.25rem;">
    <nav class="mono" style="font-size: 11px; color: var(--ink-mute); display: flex; gap: 0.5rem; align-items: center;">
        <a href="{{ route('home') }}" class="nav-link" style="padding: 0;">{{ __('Home') }}</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('marketing.contracts') }}" class="nav-link" style="padding: 0;">{{ __('Contracts') }}</a>
        <span aria-hidden="true">›</span>
        <span style="color: var(--ink);">{{ $isAr ? $t['name_ar'] : $t['short_en'] }}</span>
    </nav>
</div>

{{-- Hero --}}
<section class="section" style="padding-top: 2rem;">
    <div class="wrap">
        <span class="badge-tag" style="margin-bottom: 1.25rem;">
            <span class="pip"></span>{{ $t['short_en'] }} · {{ __('Contract template') }}
        </span>
        <h1 class="display-serif" style="font-size: clamp(36px, 5.4vw, 60px); margin: 1rem 0 1.5rem; max-width: 22ch;">
            @if ($isAr)
                صياغة <span style="color: var(--navy);">{{ $t['name_ar'] }}</span> ثنائية اللغة لكل ولايات الشرق الأوسط.
            @else
                Draft a <span style="color: var(--navy);">{{ $t['name_en'] }}</span> for any MENA jurisdiction.
            @endif
        </h1>
        <p class="lede">
            {{ $isAr ? $t['tagline_ar'] : $t['tagline_en'] }}
        </p>
        <p style="margin-top: 1.25rem; font-size: 14.5px; color: var(--ink-soft); max-width: 38em; line-height: 1.65;">
            <strong style="color: var(--ink); font-weight: 600;">{{ __('Use cases') }}:</strong>
            {{ $isAr ? $t['use_cases_ar'] : $t['use_cases_en'] }}
        </p>
        <p class="mono" style="margin-top: 1.25rem; font-size: 11.5px; color: var(--ink-mute);">
            {{ __('Last updated') }}: {{ $lastUpdated }} · {{ count($jurs) }} {{ __('jurisdictions') }} · {{ count($t['key_clauses']) }} {{ __('key clauses') }}
        </p>
    </div>
</section>

{{-- Key clauses --}}
<section class="section section-surface">
    <div class="wrap">
        <div class="head-block" style="margin-bottom: 2.5rem;">
            <span class="eyebrow"><span style="display:inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--navy);"></span>{{ __('Key clauses') }}</span>
            <h2>{{ __('What every :type contains', ['type' => $isAr ? $t['name_ar'] : $t['short_en']]) }}</h2>
        </div>
        <div style="display: grid; gap: 0.85rem; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
            @foreach ($t['key_clauses'] as $i => $clause)
                <div class="card-base" style="padding: 1.1rem 1.25rem; background: var(--canvas);">
                    <div style="display: flex; align-items: baseline; gap: 0.6rem; margin-bottom: 0.4rem;">
                        <span class="mono" style="font-size: 11px; font-weight: 600; color: var(--navy);">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 style="font-family: var(--f-sans); font-size: 14.5px; font-weight: 600; color: var(--ink); margin: 0; letter-spacing: -0.005em;">
                            {{ $isAr ? $clause['ar'] : $clause['en'] }}
                        </h3>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Per-jurisdiction list --}}
<section class="section">
    <div class="wrap">
        <div class="head-block" style="margin-bottom: 2.5rem;">
            <span class="eyebrow"><span style="display:inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--navy);"></span>{{ __('By jurisdiction') }}</span>
            <h2>{{ __(':type, country by country', ['type' => $isAr ? $t['name_ar'] : $t['name_en']]) }}</h2>
            <p class="lede">{{ __('Each jurisdiction has its own civil code, companies law, and citation form. Click through for the version that cites the controlling articles correctly.') }}</p>
        </div>
        <div style="display: grid; gap: 0.85rem; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
            @foreach ($jurs as $iso => $j)
                <a href="{{ route('marketing.contract-jurisdiction', ['type' => $type, 'iso' => $iso]) }}" class="jur-card">
                    <div class="jur-head">
                        <span class="flag" aria-hidden="true">{{ $j['flag'] }}</span>
                        <span class="nm {{ $isAr ? 'ar' : '' }}">{{ $isAr ? $j['ar'] : $j['en'] }}</span>
                        <span class="iso">{{ $iso }}</span>
                    </div>
                    <p class="ref-line">
                        @if (isset($samples[$iso]['articles']))
                            {{ __('Cites') }}: {{ implode(', ', array_slice($samples[$iso]['articles'], 0, 2)) }}@if (count($samples[$iso]['articles']) > 2)…@endif
                        @endif
                    </p>
                    <p class="ref-cite">{{ $j['code_en'] }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="section" style="text-align: center; border-top: 1px solid var(--hairline);">
    <div class="wrap-narrow">
        <h2 class="display-serif" style="font-size: clamp(28px, 4vw, 44px); margin: 0;">
            {{ __('Draft your :type now.', ['type' => $isAr ? $t['name_ar'] : $t['short_en']]) }}
        </h2>
        <p class="lede" style="margin: 1.25rem auto 2rem;">
            {{ __('Pick the jurisdiction, describe the deal, and get a bilingual draft with verified citations.') }}
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
