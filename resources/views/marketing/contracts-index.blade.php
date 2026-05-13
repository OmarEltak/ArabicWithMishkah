<?php
$cfg = config('contract_seo');
$types = $cfg['types'];
$jurs = $cfg['jurisdictions'];

$locale = app()->getLocale();
$isAr = $locale === 'ar';
$canonical = url('/contracts');

$pageTitle = $isAr
    ? 'قوالب العقود · ١١ ولاية في الشرق الأوسط · My-lawyer'
    : 'Contract templates · 11 MENA jurisdictions · My-lawyer';
$pageDescription = $isAr
    ? 'قوالب عقود قانونية للشرق الأوسط: بيع أسهم، تفاهم، توظيف، خدمات، عدم إفصاح، إيجار، توزيع، مساهمين. مرتبطة بالقوانين الفعلية في ١١ ولاية. تصدير ثنائي اللغة.'
    : 'Bilingual contract templates for MENA: SPA, MoU, employment, services, NDA, lease, distribution, shareholders. Grounded in the actual statutes of 11 jurisdictions. Civil-law glossary lock.';

$jsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'CollectionPage',
            'name' => $pageTitle,
            'description' => $pageDescription,
            'url' => $canonical,
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Contracts', 'item' => url('/contracts')],
            ],
        ],
        [
            '@type' => 'ItemList',
            'name' => 'Contract templates',
            'numberOfItems' => count($types),
            'itemListElement' => array_values(array_map(fn ($slug, $t) => [
                '@type' => 'ListItem',
                'position' => array_search($slug, array_keys($types)) + 1,
                'name' => $t['name_en'],
                'url' => url("/contracts/{$slug}"),
            ], array_keys($types), $types)),
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

{{-- Hero --}}
<section class="section">
    <div class="wrap">
        <span class="badge-tag" style="margin-bottom: 1.25rem;">
            <span class="pip"></span>{{ __('Contract library') }}
        </span>
        <h1 class="display-serif" style="font-size: clamp(36px, 5.4vw, 64px); margin: 1rem 0 1.5rem; max-width: 18ch;">
            @if ($isAr)
                {{ count($types) }} نوعاً من العقود، <span style="color: var(--navy);">{{ count($jurs) }} ولاية قضائية</span>.
            @else
                {{ count($types) }} contract types, <span style="color: var(--navy);">{{ count($jurs) }} jurisdictions</span>.
            @endif
        </h1>
        <p class="lede">
            @if ($isAr)
                كل قالب مرتبط بنصوص القانون المدني وقانون الشركات في كل ولاية قضائية. اختر النوع، اختر الدولة، احصل على عقد ثنائي اللغة موثّق الاستشهادات.
            @else
                Every template is grounded in the civil code and companies law of the chosen jurisdiction. Pick a type, pick a country, get a bilingual citation-grounded contract.
            @endif
        </p>
        <p class="mono" style="margin-top: 1rem; font-size: 11.5px; color: var(--ink-mute);">
            {{ __('Last updated') }}: {{ $lastUpdated }}
        </p>
    </div>
</section>

{{-- Type cards --}}
<section style="padding-bottom: clamp(3.5rem, 6vw, 5.5rem);">
    <div class="wrap">
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
            @foreach ($types as $slug => $t)
                <a href="{{ route('marketing.contract-type', ['type' => $slug]) }}" class="jur-card" style="padding: 1.4rem 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.85rem;">
                        <span class="badge-tag" style="font-size: 10px; padding: 0.25rem 0.65rem;">{{ $t['short_en'] }}</span>
                        <span class="iso" style="margin-inline-start: auto; font-family: var(--f-mono); font-size: 10.5px; color: var(--ink-mute);">{{ count($jurs) }} {{ __('jurisdictions') }}</span>
                    </div>
                    <h3 style="font-family: var(--f-serif); font-size: 19px; font-weight: 600; color: var(--ink); margin: 0 0 0.6rem; letter-spacing: -0.015em;">
                        {{ $isAr ? $t['name_ar'] : $t['name_en'] }}
                    </h3>
                    <p style="margin: 0; font-size: 13.5px; line-height: 1.6; color: var(--ink-soft);">
                        {{ $isAr ? $t['tagline_ar'] : $t['tagline_en'] }}
                    </p>
                    <span style="display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 1rem; font-size: 12.5px; font-weight: 600; color: var(--navy);">
                        {{ __('Browse :n versions', ['n' => count($jurs)]) }}
                        <span aria-hidden="true">{{ $isAr ? '←' : '→' }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Pull quote --}}
<section style="padding-bottom: clamp(3rem, 5vw, 4.5rem);">
    <div class="wrap">
        <div class="quote-card">
            <span class="qmark" aria-hidden="true">"</span>
            <div>
                <h3 style="font-family: var(--f-serif); font-size: 21px; font-weight: 600; letter-spacing: -0.015em; margin: 0 0 0.85rem; color: var(--ink);">{{ __('Why per-jurisdiction templates matter') }}</h3>
                <p style="margin: 0; font-size: 16px; line-height: 1.7; color: var(--ink); max-width: 56em;">
                    @if ($isAr)
                        قانون الشركات الإماراتي يختلف عن قانون الشركات السعودي يختلف عن قانون الشركات المصري. الاستشهاد بالمواد الصحيحة يفصل بين عقد قابل للتسجيل وآخر يُرفض في الشهر العقاري.
                    @else
                        UAE Companies Law differs from Saudi Companies Law differs from Egyptian Companies Law. Citing the right articles is the difference between a registrable contract and one that gets rejected at the registry.
                    @endif
                </p>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="section" style="text-align: center; border-top: 1px solid var(--hairline);">
    <div class="wrap-narrow">
        <h2 class="display-serif" style="font-size: clamp(28px, 4vw, 44px); margin: 0;">
            {{ __('Draft your first bilingual contract.') }}
        </h2>
        <p class="lede" style="margin: 1.25rem auto 2rem;">
            {{ __('Pick a template, describe the deal in Arabic, and get a citation-grounded draft you can export bilingually in 60 seconds.') }}
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
