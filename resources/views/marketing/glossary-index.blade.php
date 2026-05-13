<?php
$jurisdictions = [
    'eg' => ['name' => 'Egypt',                  'arabic' => 'مصر',                       'flag' => '🇪🇬', 'kw' => 'Egyptian civil-law glossary, قانون مدني'],
    'sa' => ['name' => 'Saudi Arabia',           'arabic' => 'المملكة العربية السعودية',  'flag' => '🇸🇦', 'kw' => 'Saudi nizam glossary'],
    'ae' => ['name' => 'United Arab Emirates',   'arabic' => 'الإمارات',                   'flag' => '🇦🇪', 'kw' => 'UAE Federal Decree-Law glossary'],
    'kw' => ['name' => 'Kuwait',                 'arabic' => 'الكويت',                     'flag' => '🇰🇼', 'kw' => 'Kuwait commercial law glossary'],
    'qa' => ['name' => 'Qatar',                  'arabic' => 'قطر',                        'flag' => '🇶🇦', 'kw' => 'Qatari civil code glossary, QFC'],
    'bh' => ['name' => 'Bahrain',                'arabic' => 'البحرين',                    'flag' => '🇧🇭', 'kw' => 'Bahrain commercial companies law glossary'],
    'om' => ['name' => 'Oman',                   'arabic' => 'عُمان',                       'flag' => '🇴🇲', 'kw' => 'Omani civil transactions law glossary'],
    'jo' => ['name' => 'Jordan',                 'arabic' => 'الأردن',                     'flag' => '🇯🇴', 'kw' => 'Jordanian civil code glossary'],
    'lb' => ['name' => 'Lebanon',                'arabic' => 'لبنان',                      'flag' => '🇱🇧', 'kw' => 'Lebanese code of obligations glossary'],
];
$available = [];
foreach ($jurisdictions as $iso => $meta) {
    if (is_file(resource_path('legal/translation-glossary-'.$iso.'.md'))) {
        $available[$iso] = $meta;
    }
}

$locale = app()->getLocale();
$isAr = $locale === 'ar';
$canonical = url('/glossary');
$pageTitle = $isAr
    ? 'مسرد الترجمة القانونية ثنائي اللغة · ولايات الشرق الأوسط · My-lawyer'
    : 'Bilingual legal-translation glossary · MENA jurisdictions · My-lawyer';
$pageDescription = $isAr
    ? 'مسارد قانونية معتمدة عربي ↔ إنجليزي لتسع ولايات في الشرق الأوسط. مصطلحات القانون المدني مع مراجع النصوص الحاكمة. تصفّح مجاني.'
    : 'Authoritative Arabic ↔ English legal-translation glossaries for 9 MENA jurisdictions. Civil-law terminology with controlling-statute references. Free to browse.';
?>

<x-marketing.layout
    :page-title="$pageTitle"
    :page-description="$pageDescription"
    :canonical="$canonical"
>

{{-- Hero --}}
<section class="section">
    <div class="wrap">
        <span class="badge-tag" style="margin-bottom: 1.25rem;">
            <span class="pip"></span>{{ __('Glossary index') }}
        </span>
        <h1 class="display-serif" style="font-size: clamp(36px, 5.4vw, 64px); margin: 1rem 0 1.5rem; max-width: 18ch;">
            @if ($isAr)
                المصطلحات القانونية ثنائية اللغة، <span style="color: var(--navy);">مقفلة</span>.
            @else
                Bilingual legal terminology, <span style="color: var(--navy);">locked</span>.
            @endif
        </h1>
        <p class="lede">
            {{ __('Curated Arabic ↔ English glossaries for civil-law contract terminology across :n MENA jurisdictions. Each term mapped to its controlling statute, with notes for ambiguous translations. Used internally by the My-lawyer translator to enforce terminology consistency.', ['n' => count($available)]) }}
        </p>
    </div>
</section>

{{-- Glossary cards --}}
<section style="padding-bottom: clamp(3.5rem, 6vw, 5.5rem);">
    <div class="wrap">
        <div style="display: grid; gap: 0.85rem; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
            @foreach ($available as $iso => $meta)
                <a href="{{ route('marketing.glossary-jurisdiction', ['iso' => $iso]) }}" class="jur-card">
                    <div class="jur-head">
                        <span class="flag" aria-hidden="true">{{ $meta['flag'] }}</span>
                        <span class="nm {{ $isAr ? 'ar' : '' }}">{{ $isAr ? $meta['arabic'] : $meta['name'] }}</span>
                        <span class="iso">{{ strtoupper($iso) }}</span>
                    </div>
                    <p class="ref-line">{{ $meta['kw'] }}</p>
                    <span style="display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 1rem; font-size: 12.5px; font-weight: 600; color: var(--navy);">
                        {{ __('Browse glossary') }}
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
                <h3 style="font-family: var(--f-serif); font-size: 21px; font-weight: 600; letter-spacing: -0.015em; margin: 0 0 0.85rem; color: var(--ink);">{{ __('Why a glossary, not a translation memory?') }}</h3>
                <p style="margin: 0; font-size: 16px; line-height: 1.7; color: var(--ink); max-width: 56em;">
                    {{ __('A translation memory remembers what someone wrote before. A glossary lock asserts what a term means under the controlling statute. In civil law, "ḥusn al-niyya" is good faith — broader than its common-law equivalent — and that distinction has to survive every draft, every reviewer, every export.') }}
                </p>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="section" style="text-align: center;">
    <div class="wrap-narrow">
        <h2 class="display-serif" style="font-size: clamp(28px, 4vw, 44px); margin: 0; max-width: none;">
            {{ __('Apply this glossary to a real contract.') }}
        </h2>
        <p class="lede" style="margin: 1.25rem auto 2rem;">
            {{ __('My-lawyer applies these glossaries automatically in every draft — every term locked, every statute cited verbatim, every translation reviewable side-by-side.') }}
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
