<?php
$names = [
    'EG' => ['en' => 'Egyptian',  'flag' => '🇪🇬'],
    'SA' => ['en' => 'Saudi',     'flag' => '🇸🇦'],
    'AE' => ['en' => 'UAE',       'flag' => '🇦🇪'],
    'KW' => ['en' => 'Kuwaiti',   'flag' => '🇰🇼'],
    'QA' => ['en' => 'Qatari',    'flag' => '🇶🇦'],
    'BH' => ['en' => 'Bahraini',  'flag' => '🇧🇭'],
    'OM' => ['en' => 'Omani',     'flag' => '🇴🇲'],
    'JO' => ['en' => 'Jordanian', 'flag' => '🇯🇴'],
    'LB' => ['en' => 'Lebanese',  'flag' => '🇱🇧'],
];
$friendly = $names[$iso]['en'] ?? $iso;
$flag = $names[$iso]['flag'] ?? '';

// Parse the markdown into sections + term rows. Format expected per line:
//   `arabic | english | optional note`
$lines = preg_split('/\r?\n/', $content) ?: [];
$sections = [];
$current = null;
$intro = '';
$inIntro = true;
foreach ($lines as $line) {
    if (preg_match('/^##\s+(.+)$/', $line, $m)) {
        $current = ['title' => trim($m[1]), 'terms' => []];
        $sections[] = &$current;
        unset($current);
        $current = &$sections[count($sections) - 1];
        $inIntro = false;
        continue;
    }
    if ($inIntro && trim($line) !== '' && ! str_starts_with(trim($line), '#') && ! str_starts_with(trim($line), 'Format:')) {
        $intro .= trim($line).' ';
    }
    if (str_contains($line, ' | ') && $current !== null) {
        $parts = array_map('trim', explode(' | ', $line, 3));
        if (count($parts) >= 2 && $parts[0] !== '' && ! str_starts_with($parts[0], '`')) {
            $current['terms'][] = [
                'ar' => $parts[0],
                'en' => $parts[1],
                'note' => $parts[2] ?? null,
            ];
        }
    }
}

$totalTerms = array_sum(array_map(fn ($s) => count($s['terms']), $sections));

$locale = app()->getLocale();
$isAr = $locale === 'ar';
$arabicNames = [
    'EG' => 'مصري', 'SA' => 'سعودي', 'AE' => 'إماراتي', 'KW' => 'كويتي',
    'QA' => 'قطري', 'BH' => 'بحريني', 'OM' => 'عُماني', 'JO' => 'أردني',
    'LB' => 'لبناني',
];
$arFriendly = $arabicNames[$iso] ?? $iso;
$canonical = url("/glossary/".strtolower($iso));
$pageTitle = $isAr
    ? "مصطلحات قانونية {$arFriendly} · عربي ↔ إنجليزي · My-lawyer"
    : "{$friendly} legal terminology · Arabic ↔ English · My-lawyer";
$pageDescription = $isAr
    ? "مسرد ترجمة معتمد للقانون المدني {$arFriendly}. أكثر من {$totalTerms} مصطلحاً بالعربية مع مكافئاتها الإنجليزية ومراجع المواد القانونية. مُصمَّم لمستشاري الشركات."
    : "Authoritative {$friendly} civil-law translation glossary. {$totalTerms}+ terms in Arabic with English equivalents and statute references. Built for corporate counsel.";

$jsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'DefinedTermSet',
    'name' => "{$friendly} legal-translation glossary (Arabic ↔ English)",
    'description' => $pageDescription,
    'inLanguage' => ['ar', 'en'],
    'numberOfTerms' => $totalTerms,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>

<x-marketing.layout
    :page-title="$pageTitle"
    :page-description="$pageDescription"
    :canonical="$canonical"
    :json-ld="$jsonLd"
>

<style>
    .gloss-section { margin-bottom: 3rem; }
    .gloss-section h2 {
        font-family: var(--f-serif);
        font-size: clamp(20px, 2.4vw, 26px);
        font-weight: 600;
        letter-spacing: -0.015em;
        color: var(--ink);
        margin: 0 0 1rem;
    }
    .gloss-table {
        width: 100%;
        border-collapse: collapse;
        background: var(--canvas);
        border: 1px solid var(--hairline);
        border-radius: 12px;
        overflow: hidden;
    }
    .gloss-table thead {
        background: var(--surface);
        border-bottom: 1px solid var(--hairline);
    }
    .gloss-table thead th {
        padding: 0.75rem 1rem;
        text-align: start;
        font-family: var(--f-mono);
        font-size: 10.5px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--ink-mute);
    }
    .gloss-table tbody tr { border-top: 1px solid var(--hairline-soft); }
    .gloss-table tbody tr:first-child { border-top: 0; }
    .gloss-table tbody tr:hover { background: var(--surface); }
    .gloss-table td {
        padding: 0.85rem 1rem;
        vertical-align: top;
        font-size: 13.5px;
        line-height: 1.55;
    }
    .gloss-table td.col-ar {
        font-family: var(--f-arabic);
        font-size: 15.5px;
        font-weight: 500;
        color: var(--ink);
        width: 32%;
    }
    .gloss-table td.col-en {
        color: var(--ink);
        font-weight: 500;
        width: 32%;
    }
    .gloss-table td.col-note {
        color: var(--ink-mute);
        font-style: italic;
        font-size: 12.5px;
    }
</style>

{{-- Hero --}}
<section class="section">
    <div class="wrap-narrow">
        <span class="badge-tag" style="margin-bottom: 1.25rem;">
            <span class="pip"></span>{{ __('Glossary') }} · {{ $iso }}
        </span>
        <h1 class="display-serif" style="font-size: clamp(34px, 4.8vw, 56px); margin: 1rem 0 0.75rem;">
            <span style="font-size: 0.95em; margin-inline-end: 0.25em; vertical-align: -0.05em;">{{ $flag }}</span>
            {{ $friendly }} {{ __('legal-translation glossary') }}
        </h1>
        <p style="font-family: var(--f-serif); font-size: clamp(20px, 2.4vw, 26px); font-weight: 400; color: var(--ink-mute); letter-spacing: -0.015em; margin: 0.4rem 0 1.5rem;">
            {{ __('Arabic ↔ English') }}
        </p>
        <p class="lede">
            {{ __('Authoritative terminology lock used by the My-lawyer translator. :n terms across :s sections. Each entry mapped to its controlling statute where applicable.', ['n' => $totalTerms, 's' => count($sections)]) }}
        </p>
        <p style="margin-top: 1.5rem;">
            <a href="{{ route('marketing.glossary') }}" class="btn-link">
                <span aria-hidden="true">{{ $isAr ? '→' : '←' }}</span>
                {{ __('All jurisdictions') }}
            </a>
        </p>
    </div>
</section>

{{-- Sections --}}
<section style="padding-bottom: clamp(3rem, 5vw, 4.5rem);">
    <div class="wrap-narrow">
        @foreach ($sections as $section)
            @if (count($section['terms']) === 0)
                @continue
            @endif
            <div class="gloss-section">
                <h2>{{ $section['title'] }}</h2>
                <div style="overflow-x: auto;">
                    <table class="gloss-table">
                        <thead>
                            <tr>
                                <th>{{ __('Arabic') }}</th>
                                <th>{{ __('English') }}</th>
                                <th>{{ __('Note') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($section['terms'] as $term)
                                <tr>
                                    <td class="col-ar" dir="rtl">{{ $term['ar'] }}</td>
                                    <td class="col-en" dir="ltr">{{ $term['en'] }}</td>
                                    <td class="col-note" dir="ltr">{{ $term['note'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- CTA --}}
<section class="section" style="text-align: center;">
    <div class="wrap-narrow">
        <h2 class="display-serif" style="font-size: clamp(26px, 3.6vw, 40px); margin: 0;">
            {{ __('Translate a real :country contract.', ['country' => $friendly]) }}
        </h2>
        <p class="lede" style="margin: 1.25rem auto 2rem;">
            {{ __('My-lawyer applies this glossary automatically — every term locked, every statute cited verbatim, every translation reviewable side-by-side.') }}
        </p>
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.85rem;">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">{{ __('Open dashboard') }}</a>
            @endauth
            @guest
                <a href="{{ route('register') }}" class="btn-primary">{{ __('Start drafting free') }}</a>
            @endguest
        </div>
    </div>
</section>

</x-marketing.layout>
