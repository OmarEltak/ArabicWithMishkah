<?php
use App\Models\LegalDocument;
use App\Models\ContractTemplate;

// $iso is passed in from the route. ISO → English + Arabic name.
$names = [
    'EG' => ['English' => 'Egypt',        'arabic' => 'مصر',                   'short' => 'Egyptian',        'capital' => 'Cairo',     'court' => 'Cairo Economic Courts',         'codeName' => 'Egyptian Civil Code (Law No. 131 of 1948)',                                'flag' => '🇪🇬'],
    'SA' => ['English' => 'Saudi Arabia', 'arabic' => 'المملكة العربية السعودية', 'short' => 'Saudi',          'capital' => 'Riyadh',    'court' => 'Saudi Commercial Courts',       'codeName' => 'Saudi Civil Transactions Law (Royal Decree No. M/191 of 1444H)',           'flag' => '🇸🇦'],
    'AE' => ['English' => 'United Arab Emirates', 'arabic' => 'الإمارات العربية المتحدة', 'short' => 'Emirati', 'capital' => 'Abu Dhabi', 'court' => 'UAE Federal & Local Courts (DIFC / ADGM optional)', 'codeName' => 'UAE Civil Transactions Law (Federal Law No. 5 of 1985, as amended)',         'flag' => '🇦🇪'],
    'KW' => ['English' => 'Kuwait',       'arabic' => 'دولة الكويت',           'short' => 'Kuwaiti',         'capital' => 'Kuwait City', 'court' => 'Kuwait Civil & Commercial Courts', 'codeName' => 'Kuwaiti Civil Code (Decree-Law No. 67 of 1980)',                          'flag' => '🇰🇼'],
    'QA' => ['English' => 'Qatar',        'arabic' => 'دولة قطر',              'short' => 'Qatari',          'capital' => 'Doha',      'court' => 'Qatar Courts (QFC option for international)', 'codeName' => 'Qatari Civil Code (Law No. 22 of 2004)',                                  'flag' => '🇶🇦'],
    'BH' => ['English' => 'Bahrain',      'arabic' => 'مملكة البحرين',         'short' => 'Bahraini',        'capital' => 'Manama',    'court' => 'Bahrain Courts (BCDR for arbitration)', 'codeName' => 'Bahrain Civil Code (Decree-Law No. 19 of 2001)',                          'flag' => '🇧🇭'],
    'OM' => ['English' => 'Oman',         'arabic' => 'سلطنة عُمان',           'short' => 'Omani',           'capital' => 'Muscat',    'court' => 'Omani Civil Courts',           'codeName' => 'Omani Civil Transactions Law (Royal Decree 29/2013)',                       'flag' => '🇴🇲'],
    'JO' => ['English' => 'Jordan',       'arabic' => 'المملكة الأردنية الهاشمية', 'short' => 'Jordanian',     'capital' => 'Amman',     'court' => 'Jordanian Civil Courts',       'codeName' => 'Jordanian Civil Code (Law No. 43 of 1976)',                                'flag' => '🇯🇴'],
    'LB' => ['English' => 'Lebanon',      'arabic' => 'الجمهورية اللبنانية',   'short' => 'Lebanese',        'capital' => 'Beirut',    'court' => 'Lebanese Civil Courts',        'codeName' => 'Lebanese Code of Obligations and Contracts (1932)',                        'flag' => '🇱🇧'],
    'TN' => ['English' => 'Tunisia',      'arabic' => 'الجمهورية التونسية',    'short' => 'Tunisian',        'capital' => 'Tunis',     'court' => 'Tunisian Civil Courts',        'codeName' => 'Tunisian Code of Obligations and Contracts',                                'flag' => '🇹🇳'],
    'LY' => ['English' => 'Libya',        'arabic' => 'دولة ليبيا',            'short' => 'Libyan',          'capital' => 'Tripoli',   'court' => 'Libyan Civil Courts',          'codeName' => 'Libyan Civil Code',                                                         'flag' => '🇱🇾'],
];
$j = $names[$iso] ?? null;
abort_if($j === null, 404);

$officialSlugs = (array) config('legal_sources.official_slugs', ['eastlaws']);
$docCount = LegalDocument::query()->where('jurisdiction', $iso)->whereIn('source', $officialSlugs)->count();
$templates = ContractTemplate::query()->where('is_system', true)->where('jurisdiction', $iso)->get();
$tplCount = $templates->count();
$categories = LegalDocument::query()
    ->where('jurisdiction', $iso)
    ->whereIn('source', $officialSlugs)
    ->whereNotNull('category')
    ->select('category')
    ->selectRaw('count(*) as n')
    ->groupBy('category')
    ->orderByDesc('n')
    ->limit(10)
    ->get();

$canonical = url("/jurisdictions/{$iso}");
$locale = app()->getLocale();
$isAr = $locale === 'ar';
$displayName = $isAr ? $j['arabic'] : $j['English'];
$pageTitle = $isAr
    ? "صياغة عقود {$j['arabic']} · My-lawyer"
    : "{$j['short']} contract drafting · My-lawyer";
$pageDescription = $isAr
    ? "صياغة عقود مدعومة بالذكاء الاصطناعي وفقاً لقانون {$j['arabic']}. {$docCount} وثيقة قانونية في الفهرس، {$tplCount} قالب جاهز للتعبئة، تصدير ثنائي اللغة عربي/إنجليزي. بنود موثّقة بالاستشهادات."
    : "AI-grounded contract drafting under {$j['English']} law. {$docCount} indexed legal documents, {$tplCount} ready-to-fill templates, bilingual Arabic / English export. Citation-verified clauses backed by the {$j['codeName']}.";

$jsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => "Contract drafting for {$j['English']}",
    'serviceType' => 'AI legal drafting',
    'provider' => ['@type' => 'Organization', 'name' => 'My-lawyer'],
    'areaServed' => ['@type' => 'Country', 'name' => $j['English']],
    'description' => $pageDescription,
    'inLanguage' => ['ar', 'en'],
    'audience' => ['@type' => 'BusinessAudience', 'audienceType' => "Corporate counsel in {$j['English']}"],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
            <span class="badge-tag">
                <span class="pip"></span>{{ __('Jurisdiction') }} · {{ $iso }}
            </span>
            <x-coverage-badge :iso="$iso" size="lg" />
        </div>
        <h1 class="display-serif" style="font-size: clamp(36px, 5.4vw, 64px); margin: 1rem 0 1.5rem; max-width: 18ch;">
            <span style="font-size: 1.05em; margin-inline-end: 0.4em; vertical-align: -0.05em;">{{ $j['flag'] }}</span>
            @if ($isAr)
                صياغة العقود وفقاً لقانون <span style="color: var(--navy);">{{ $displayName }}</span>.
            @else
                Contract drafting under <span style="color: var(--navy);">{{ $displayName }}</span> law.
            @endif
        </h1>
        <p class="lede">
            {{ __('My-lawyer drafts and translates contracts grounded in :code. Every clause cites the controlling article. Output exports as a bilingual Arabic / English document with the language-prevailing clause auto-appended.', ['code' => $j['codeName']]) }}
        </p>
        @php($coverageLevel = config('coverage.jurisdictions.'.$iso, 'preview'))
        @php($coverageNote = $isAr ? config('coverage.levels.'.$coverageLevel.'.note_ar') : config('coverage.levels.'.$coverageLevel.'.note_en'))
        @if ($coverageLevel !== 'live')
            <div style="margin-top: 1.25rem; padding: 0.85rem 1.1rem; background: {{ $coverageLevel === 'beta' ? '#FBF1D8' : '#E9EDF2' }}; border-radius: 8px; font-size: 13.5px; line-height: 1.55; color: {{ $coverageLevel === 'beta' ? '#7A5A0F' : '#4B5868' }}; max-width: 38em;">
                <strong>{{ $isAr ? 'حالة التغطية' : 'Coverage status' }}:</strong> {{ $coverageNote }}
            </div>
        @endif
        <div style="display: flex; flex-wrap: wrap; gap: 0.85rem; margin-top: 2rem;">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">{{ __('Open dashboard') }}</a>
            @endauth
            @guest
                <a href="{{ route('register') }}" class="btn-primary">{{ __('Start drafting free') }}</a>
                <a href="{{ route('marketing.glossary-jurisdiction', ['iso' => strtolower($iso)]) }}" class="btn-outline">
                    {{ __('See :country glossary', ['country' => $displayName]) }}
                    <span aria-hidden="true">{{ $isAr ? '←' : '→' }}</span>
                </a>
            @endguest
        </div>
    </div>
</section>

{{-- Stats strip --}}
<section class="section-surface" style="padding: 1.5rem 0;">
    <div class="wrap" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem;">
        <div style="display: flex; align-items: center; gap: 0.85rem; padding: 0.5rem 0;">
            <span class="stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="14 3 14 9 20 9"/></svg>
            </span>
            <div>
                <div style="font-family: var(--f-serif); font-size: 28px; font-weight: 600; letter-spacing: -0.02em; line-height: 1; color: var(--ink);">{{ number_format($docCount) }}</div>
                <div style="font-size: 12.5px; color: var(--ink-soft); margin-top: 4px;">{{ __('Documents indexed') }}</div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.85rem; padding: 0.5rem 0;">
            <span class="stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="14 3 14 9 20 9"/><path d="M9 13h6"/><path d="M9 17h4"/></svg>
            </span>
            <div>
                <div style="font-family: var(--f-serif); font-size: 28px; font-weight: 600; letter-spacing: -0.02em; line-height: 1; color: var(--ink);">{{ $tplCount }}</div>
                <div style="font-size: 12.5px; color: var(--ink-soft); margin-top: 4px;">{{ __('Templates') }}</div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.85rem; padding: 0.5rem 0;">
            <span class="stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="10" r="3"/><path d="M12 22s-8-7-8-12a8 8 0 1 1 16 0c0 5-8 12-8 12z"/></svg>
            </span>
            <div>
                <div style="font-family: var(--f-serif); font-size: 22px; font-weight: 600; letter-spacing: -0.02em; line-height: 1; color: var(--ink);">{{ $j['capital'] }}</div>
                <div style="font-size: 12.5px; color: var(--ink-soft); margin-top: 4px;">{{ __('Capital') }}</div>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 0.85rem; padding: 0.5rem 0;">
            <span class="stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15 15 0 0 1 4 10 15 15 0 0 1-4 10 15 15 0 0 1-4-10 15 15 0 0 1 4-10z"/></svg>
            </span>
            <div>
                <div style="font-family: var(--f-arabic); font-size: 19px; font-weight: 700; line-height: 1; color: var(--ink);" dir="rtl">{{ $j['arabic'] }}</div>
                <div style="font-size: 12.5px; color: var(--ink-soft); margin-top: 4px;">{{ __('Local name') }}</div>
            </div>
        </div>
    </div>
</section>

{{-- Categories --}}
@if ($categories->count() > 0)
<section class="section">
    <div class="wrap">
        <div class="head-block" style="margin-bottom: 2.5rem;">
            <span class="eyebrow"><span style="display:inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--navy);"></span>{{ __('Coverage') }}</span>
            <h2>{{ __(':country legal authority by domain', ['country' => $displayName]) }}</h2>
            <p class="lede">{{ __('Every drafting query is grounded in this body of indexed authority. The drafter retrieves only documents from the requested jurisdiction unless cross-jurisdictional citation is explicitly requested.') }}</p>
        </div>
        @php($maxN = $categories->max('n'))
        <div style="display: grid; gap: 0.85rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            @foreach ($categories as $row)
                @php($pct = $maxN > 0 ? (int) round($row->n / $maxN * 100) : 0)
                <div class="card-base" style="padding: 1rem 1.25rem;">
                    <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 0.5rem;">
                        <span class="mono" style="font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-soft);">{{ $row->category }}</span>
                        <span style="font-family: var(--f-serif); font-size: 18px; font-weight: 600; color: var(--ink);">{{ $row->n }}</span>
                    </div>
                    <div style="margin-top: 0.7rem; height: 4px; width: 100%; overflow: hidden; border-radius: 9999px; background: var(--surface-2);">
                        <div style="height: 100%; border-radius: 9999px; background: var(--navy); width: {{ $pct }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Templates --}}
@if ($templates->count() > 0)
<section class="section section-surface">
    <div class="wrap">
        <div class="head-block" style="margin-bottom: 2.5rem;">
            <span class="eyebrow"><span style="display:inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--navy);"></span>{{ __('Templates') }}</span>
            <h2>{{ __(':n contract templates ready for :country deals', ['n' => $tplCount, 'country' => $displayName]) }}</h2>
        </div>
        <div style="display: grid; gap: 0.85rem; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
            @foreach ($templates as $tpl)
                <article class="jur-card">
                    <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.5rem;">
                        <h3 style="font-family: var(--f-serif); font-size: 16px; font-weight: 600; color: var(--ink); margin: 0;">{{ $tpl->name }}</h3>
                        @if ($tpl->category)
                            <span class="mono" style="font-size: 10.5px; letter-spacing: 0.05em; color: var(--ink-mute); text-transform: uppercase;">{{ $tpl->category }}</span>
                        @endif
                    </div>
                    @if ($tpl->description)
                        <p style="margin: 0; font-size: 13px; line-height: 1.6; color: var(--ink-soft);">{{ $tpl->description }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- CTA --}}
<section class="section" style="text-align: center;">
    <div class="wrap-narrow">
        <h2 class="display-serif" style="font-size: clamp(28px, 4vw, 44px); margin: 0; max-width: none;">
            {{ __('Draft a :country contract in minutes.', ['country' => $displayName]) }}
        </h2>
        <p class="lede" style="margin: 1.25rem auto 2rem;">
            {{ __('Pick a template, describe the deal in Arabic, and get a citation-grounded draft you can export bilingually.') }}
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
