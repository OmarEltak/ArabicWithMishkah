<?php

use App\Models\ContractTemplate;
use App\Models\LegalDocument;
use Illuminate\Support\Facades\Cache;

// Cache the homepage counts for an hour — crawler spikes (Googlebot,
// link previews) would otherwise run the same three aggregates on every
// hit. `legal:seed-catalog` and ingestion code can invalidate via
// `Cache::forget('welcome.counts')` if a fresh count is needed sooner.
[$docCount, $tplCount, $jurisdictionCount] = Cache::remember('welcome.counts', 3600, function () {
    $officialSlugs = (array) config('legal_sources.official_slugs', ['eastlaws']);

    return [
        LegalDocument::query()->count(),
        ContractTemplate::query()->where('is_system', true)->count(),
        LegalDocument::query()
            ->whereNotNull('jurisdiction')
            ->whereIn('source', $officialSlugs)
            ->distinct('jurisdiction')
            ->count('jurisdiction'),
    ];
});

$locale = app()->getLocale();
$isAr = $locale === 'ar';
$dir = $isAr ? 'rtl' : 'ltr';
$themeClass = request()->cookie('theme') === 'dark' ? 'dark' : '';
$canonical = url('/');

$metaTitle = $isAr
    ? 'My-lawyer · صياغة العقود بالذكاء الاصطناعي تحت القانون المصري'
    : 'My-lawyer · AI contract drafting under Egyptian law';
$metaDesc = $isAr
    ? 'صياغة عقود ثنائية اللغة مرتبطة بالقانون المدني المصري وقوانين الشركات والعمل. توثيق الاستشهادات وقفل المصطلحات. توسعنا الإقليمي قيد التطوير.'
    : 'AI-grounded bilingual contract drafting under Egyptian Civil Code and adjacent statutes. Citation-verified, glossary-locked. Regional expansion in active development.';

$lastUpdated = '2026-05-10';
$jsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'SoftwareApplication',
            'name' => 'My-lawyer',
            'applicationCategory' => 'LegalService',
            'operatingSystem' => 'Web',
            'description' => $metaDesc,
            'url' => $canonical,
            'dateModified' => $lastUpdated,
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
            'featureList' => [
                'Bilingual Arabic/English contract drafting',
                'Citation-verified clauses',
                '11 MENA jurisdictions',
                'Civil-law glossary lock',
                'Side-by-side bilingual export',
            ],
            'inLanguage' => ['ar', 'en'],
        ],
        [
            '@type' => 'Organization',
            'name' => 'My-lawyer',
            'url' => $canonical,
            'description' => 'AI contract drafting for MENA corporate counsel.',
            'sameAs' => [
                'https://github.com/OmarEltak/bilingual-legal-translation',
            ],
        ],
        [
            '@type' => 'HowTo',
            'name' => 'How to draft a bilingual MENA contract with citation verification',
            'description' => 'Draft a contract in Arabic, ground every clause in the controlling civil code, and export bilingually.',
            'totalTime' => 'PT1M',
            'step' => [
                ['@type' => 'HowToStep', 'position' => 1, 'name' => 'Brief',                'text' => 'Upload your brief or answer a few questions about the deal and jurisdiction.'],
                ['@type' => 'HowToStep', 'position' => 2, 'name' => 'Draft in Arabic',     'text' => 'My-lawyer drafts the contract in Arabic using jurisdiction-specific language and structure, picking civil-code articles and locking civil-law terminology.'],
                ['@type' => 'HowToStep', 'position' => 3, 'name' => 'Citations verified', 'text' => 'Every clause is matched to the controlling statute. Unverifiable citations are flagged before finalization.'],
                ['@type' => 'HowToStep', 'position' => 4, 'name' => 'Bilingual export',    'text' => 'Export Arabic and English side-by-side as PDF or Word, with citations preserved and a language-prevailing clause auto-appended.'],
            ],
        ],
        [
            '@type' => 'ItemList',
            'name' => 'MENA jurisdictions covered',
            'numberOfItems' => 11,
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1,  'name' => 'Egypt',                  'url' => url('/jurisdictions/EG')],
                ['@type' => 'ListItem', 'position' => 2,  'name' => 'Saudi Arabia',           'url' => url('/jurisdictions/SA')],
                ['@type' => 'ListItem', 'position' => 3,  'name' => 'United Arab Emirates',   'url' => url('/jurisdictions/AE')],
                ['@type' => 'ListItem', 'position' => 4,  'name' => 'Kuwait',                 'url' => url('/jurisdictions/KW')],
                ['@type' => 'ListItem', 'position' => 5,  'name' => 'Qatar',                  'url' => url('/jurisdictions/QA')],
                ['@type' => 'ListItem', 'position' => 6,  'name' => 'Bahrain',                'url' => url('/jurisdictions/BH')],
                ['@type' => 'ListItem', 'position' => 7,  'name' => 'Oman',                   'url' => url('/jurisdictions/OM')],
                ['@type' => 'ListItem', 'position' => 8,  'name' => 'Jordan',                 'url' => url('/jurisdictions/JO')],
                ['@type' => 'ListItem', 'position' => 9,  'name' => 'Lebanon',                'url' => url('/jurisdictions/LB')],
                ['@type' => 'ListItem', 'position' => 10, 'name' => 'Tunisia',                'url' => url('/jurisdictions/TN')],
                ['@type' => 'ListItem', 'position' => 11, 'name' => 'Libya',                  'url' => url('/jurisdictions/LY')],
            ],
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$jurisdictions = [
    ['iso' => 'EG', 'flag' => '🇪🇬', 'en' => 'Egypt',        'ar' => 'مصر',                       'note_en' => 'Civil Code',        'note_ar' => 'القانون المدني',          'cite_en' => 'Law 131/1948',        'cite_ar' => '١٣١/١٩٤٨'],
    ['iso' => 'SA', 'flag' => '🇸🇦', 'en' => 'Saudi Arabia', 'ar' => 'المملكة العربية السعودية',   'note_en' => 'Civil Code',        'note_ar' => 'نظام المعاملات المدنية', 'cite_en' => 'Royal Decree M/191',  'cite_ar' => 'مرسوم ملكي م/١٩١'],
    ['iso' => 'AE', 'flag' => '🇦🇪', 'en' => 'UAE',          'ar' => 'الإمارات',                    'note_en' => 'Civil Transactions','note_ar' => 'المعاملات المدنية',      'cite_en' => 'Federal Decree-Law No. 5/1985', 'cite_ar' => 'القانون الاتحادي ٥/١٩٨٥'],
    ['iso' => 'KW', 'flag' => '🇰🇼', 'en' => 'Kuwait',       'ar' => 'الكويت',                     'note_en' => 'Civil Code',        'note_ar' => 'القانون المدني',          'cite_en' => 'Law 67/1980',         'cite_ar' => '٦٧/١٩٨٠'],
    ['iso' => 'QA', 'flag' => '🇶🇦', 'en' => 'Qatar',        'ar' => 'قطر',                        'note_en' => 'Civil Code',        'note_ar' => 'القانون المدني',          'cite_en' => 'Law 22/2004',         'cite_ar' => '٢٢/٢٠٠٤'],
    ['iso' => 'BH', 'flag' => '🇧🇭', 'en' => 'Bahrain',      'ar' => 'البحرين',                    'note_en' => 'Civil Code',        'note_ar' => 'القانون المدني',          'cite_en' => 'Decree-Law 19/2001',  'cite_ar' => 'المرسوم بقانون ١٩/٢٠٠١'],
    ['iso' => 'OM', 'flag' => '🇴🇲', 'en' => 'Oman',         'ar' => 'عُمان',                       'note_en' => 'Civil Transactions','note_ar' => 'المعاملات المدنية',      'cite_en' => 'Royal Decree 29/2013','cite_ar' => 'مرسوم سلطاني ٢٩/٢٠١٣'],
    ['iso' => 'JO', 'flag' => '🇯🇴', 'en' => 'Jordan',       'ar' => 'الأردن',                     'note_en' => 'Civil Code',        'note_ar' => 'القانون المدني',          'cite_en' => 'Law 43/1976',         'cite_ar' => '٤٣/١٩٧٦'],
    ['iso' => 'LB', 'flag' => '🇱🇧', 'en' => 'Lebanon',      'ar' => 'لبنان',                      'note_en' => 'Code of Obligations','note_ar' => 'قانون الموجبات والعقود', 'cite_en' => '1932',                 'cite_ar' => '١٩٣٢'],
    ['iso' => 'TN', 'flag' => '🇹🇳', 'en' => 'Tunisia',      'ar' => 'تونس',                       'note_en' => 'Code of Obligations','note_ar' => 'مجلة الالتزامات والعقود','cite_en' => '1906',                 'cite_ar' => '١٩٠٦'],
    ['iso' => 'LY', 'flag' => '🇱🇾', 'en' => 'Libya',        'ar' => 'ليبيا',                      'note_en' => 'Civil Code',        'note_ar' => 'القانون المدني',          'cite_en' => '1953',                 'cite_ar' => '١٩٥٣'],
];

$displayDocs = $docCount > 0 ? $docCount : 120000;
$displayJurs = $jurisdictionCount > 0 ? $jurisdictionCount : 11;
?>
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}" class="{{ $themeClass }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="robots" content="index, follow, max-image-preview:large">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:locale" content="{{ $isAr ? 'ar_AR' : 'en_US' }}">
    <meta property="og:locale:alternate" content="{{ $isAr ? 'en_US' : 'ar_AR' }}">
    <meta property="og:site_name" content="My-lawyer">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">

    <link rel="alternate" hreflang="ar" href="{{ $canonical }}?lang=ar">
    <link rel="alternate" hreflang="en" href="{{ $canonical }}?lang=en">
    <link rel="alternate" hreflang="x-default" href="{{ $canonical }}">

    <script type="application/ld+json">{!! $jsonLd !!}</script>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600&family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Tokens audited for WCAG AA contrast on body text (4.5:1) */
            --canvas: #FFFFFF;
            --paper: #FAFBFC;
            --surface: #F4F6F9;
            --surface-2: #E9EDF2;
            --ink: #0B1626;          /* 17.4:1 on canvas — AAA */
            --ink-soft: #3F4A5A;     /* 8.6:1 on canvas — AAA */
            --ink-mute: #5C6677;     /* 5.4:1 on canvas — AA body */
            --hairline: #DCE2EA;
            --hairline-soft: #E6EAF0;
            --navy: #0F2942;
            --navy-deep: #0A1E33;
            --navy-light: #1B3A6B;
            --navy-soft: #E1E9F6;
            --gold: #FAF3E0;
            --gold-deep: #B8932F;
            --green: #156B40;
            --green-soft: #DDF1E5;

            --f-serif:  'Source Serif 4', 'Source Serif Pro', Georgia, serif;
            --f-sans:   'Inter', system-ui, -apple-system, sans-serif;
            --f-mono:   'JetBrains Mono', ui-monospace, monospace;
            --f-arabic: 'Tajawal', 'Inter', system-ui, sans-serif;
        }

        html.dark {
            --canvas: #0A0F18;
            --paper: #131B2C;
            --surface: #131B2C;
            --surface-2: #1B243A;
            --ink: #F6F8FB;
            --ink-soft: #C5CDD9;
            --ink-mute: #98A3B5;
            --hairline: #243049;
            --hairline-soft: #1B2438;
            --navy: #0F2942;
            --navy-deep: #050D17;
            --navy-soft: rgba(140, 170, 230, 0.16);
            --gold: rgba(201, 168, 74, 0.14);
            --green: #4FB67E;
            --green-soft: rgba(79, 182, 126, 0.14);
        }

        * { box-sizing: border-box; }
        html { background: var(--canvas); }
        body {
            margin: 0;
            background: var(--canvas);
            color: var(--ink);
            font-family: var(--f-sans);
            font-feature-settings: 'ss01', 'cv11';
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            line-height: 1.5;
        }
        :where(html[dir="rtl"]) body { font-family: var(--f-arabic); }

        a { color: inherit; text-decoration: none; }
        ul { list-style: none; padding: 0; margin: 0; }
        button { font-family: inherit; }

        .ar { font-family: var(--f-arabic); }
        .en { font-family: var(--f-sans); }
        .mono { font-family: var(--f-mono); }
        .serif { font-family: var(--f-serif); }

        .wrap { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }
        @media (min-width: 768px) { .wrap { padding: 0 2.5rem; } }

        ::selection { background: var(--navy); color: #fff; }

        /* ────────  Eyebrow / Tag chip  ──────── */
        .eyebrow {
            font-family: var(--f-mono);
            font-size: 11px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--ink-mute);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }
        .badge-tag {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.35rem 0.85rem;
            background: var(--navy-soft); color: var(--navy);
            border-radius: 9999px;
            font-family: var(--f-mono);
            font-size: 11px; font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        html.dark .badge-tag { background: rgba(108, 145, 220, 0.16); color: #8FB0E5; }
        .badge-tag .pip {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--navy);
        }
        html.dark .badge-tag .pip { background: #8FB0E5; }

        /* ────────  Display type  ──────── */
        .display-serif {
            font-family: var(--f-serif);
            font-weight: 500;
            letter-spacing: -0.022em;
            line-height: 1.05;
            color: var(--ink);
        }
        :where(html[dir="rtl"]) .display-serif {
            font-family: var(--f-arabic);
            font-weight: 700;
            letter-spacing: -0.005em;
            line-height: 1.2;
        }
        .display-sans {
            font-family: var(--f-sans);
            font-weight: 600;
            letter-spacing: -0.025em;
            line-height: 1.1;
        }
        :where(html[dir="rtl"]) .display-sans {
            font-family: var(--f-arabic);
            font-weight: 700;
        }

        .lede {
            color: var(--ink-soft);
            font-size: 17px;
            line-height: 1.65;
            max-width: 32em;
            font-weight: 400;
        }
        :where(html[dir="rtl"]) .lede { font-size: 17.5px; line-height: 1.85; }

        /* ────────  Buttons  ──────── */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 0.6rem;
            padding: 0.85rem 1.4rem;
            background: var(--navy); color: #fff;
            border-radius: 10px;
            font-size: 14px; font-weight: 600;
            letter-spacing: -0.005em;
            transition: background 0.15s, transform 0.15s;
            border: 1px solid var(--navy);
            cursor: pointer;
        }
        .btn-primary:hover { background: var(--navy-deep); transform: translateY(-1px); }

        .btn-outline {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.8rem 1.3rem;
            background: var(--canvas); color: var(--ink);
            border: 1px solid var(--hairline);
            border-radius: 10px;
            font-size: 14px; font-weight: 600;
            transition: border-color 0.15s, background 0.15s;
            cursor: pointer;
        }
        .btn-outline:hover { border-color: var(--ink); background: var(--surface); }

        .btn-link {
            display: inline-flex; align-items: center; gap: 0.4rem;
            font-size: 14px; font-weight: 500;
            color: var(--ink-soft);
            transition: color 0.15s;
        }
        .btn-link:hover { color: var(--navy); }

        .nav-link {
            font-size: 14px; font-weight: 500;
            color: var(--ink-soft);
            transition: color 0.15s;
            padding: 0.4rem 0.65rem;
        }
        .nav-link:hover { color: var(--ink); }

        /* ────────  Header  ──────── */
        .nav-shell {
            position: sticky; top: 0; z-index: 40;
            background: color-mix(in oklab, var(--canvas) 88%, transparent);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--hairline);
        }

        .logo-mark {
            width: 36px; height: 36px;
            border-radius: 8px;
            background: var(--navy);
            color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .logo-text {
            font-family: var(--f-serif);
            font-size: 22px; font-weight: 600;
            letter-spacing: -0.02em;
            color: var(--ink);
        }
        :where(html[dir="rtl"]) .logo-text { font-family: var(--f-arabic); font-weight: 700; }

        /* AR/EN pill toggle */
        .lang-toggle {
            display: inline-flex; align-items: center;
            background: var(--surface);
            border: 1px solid var(--hairline);
            border-radius: 9999px;
            padding: 3px;
            position: relative;
        }
        .lang-toggle button {
            padding: 0.35rem 0.85rem;
            font-family: var(--f-mono);
            font-size: 11px; font-weight: 600;
            letter-spacing: 0.05em;
            color: var(--ink-mute);
            background: transparent;
            border: 0; border-radius: 9999px;
            cursor: pointer;
            transition: color 0.15s, background 0.15s;
        }
        .lang-toggle button.active {
            background: var(--navy); color: #fff;
        }

        /* ────────  Hero  ──────── */
        .hero {
            padding: clamp(3rem, 7vw, 5.5rem) 0 clamp(4rem, 8vw, 6.5rem);
            position: relative;
            overflow: hidden;
        }
        .hero-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            align-items: start;
            position: relative;
        }
        @media (min-width: 1000px) {
            .hero-grid { grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr); gap: 4rem; }
        }
        .hero h1 {
            font-size: clamp(38px, 5.4vw, 64px);
            margin: 1.25rem 0 1.5rem;
            max-width: 14ch;
        }
        .hero-cta-row {
            display: flex; flex-wrap: wrap; align-items: center; gap: 0.85rem;
            margin-top: 2rem;
        }
        .hero-foot {
            margin-top: 1rem;
            display: inline-flex; align-items: center; gap: 0.45rem;
            font-size: 12.5px; color: var(--ink-mute);
        }
        .hero-foot .check {
            width: 14px; height: 14px; border-radius: 50%;
            background: var(--green); color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 9px;
        }

        /* ────────  Product preview (hero right)  ──────── */
        .preview {
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 14px;
            box-shadow:
                0 1px 0 rgba(255,255,255,0.6) inset,
                0 30px 60px -30px rgba(15, 27, 45, 0.18),
                0 8px 24px -12px rgba(15, 27, 45, 0.10);
            overflow: hidden;
            display: grid;
            grid-template-columns: 44px 1fr;
            min-height: 460px;
        }
        html.dark .preview {
            box-shadow: 0 30px 60px -30px rgba(0,0,0,0.6);
        }
        .preview-side {
            background: var(--navy);
            display: flex; flex-direction: column; align-items: center;
            padding: 0.85rem 0;
            gap: 0.4rem;
        }
        .preview-side .ic {
            width: 28px; height: 28px;
            display: flex; align-items: center; justify-content: center;
            color: rgba(255,255,255,0.78);
            border-radius: 6px;
        }
        .preview-side .ic.active {
            background: rgba(255,255,255,0.18);
            color: #fff;
        }
        .preview-main { display: flex; flex-direction: column; }
        .preview-bar {
            display: flex; align-items: center; gap: 0.6rem;
            padding: 0.85rem 1.1rem;
            border-bottom: 1px solid var(--hairline-soft);
            font-size: 13px; color: var(--ink-soft);
            flex-wrap: wrap;
        }
        .preview-bar .crumb { color: var(--ink-mute); }
        .preview-bar .arrow-sep { color: var(--ink-mute); font-size: 11px; }
        .preview-bar .crumb-cur { color: var(--ink); font-weight: 500; }
        .verified-chip {
            margin-inline-start: auto;
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.35rem 0.7rem;
            background: var(--green-soft); color: var(--green);
            border-radius: 9999px;
            font-family: var(--f-mono); font-size: 11px; font-weight: 600;
        }
        .verified-chip .v-tick {
            width: 14px; height: 14px; border-radius: 50%;
            background: var(--green); color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 9px;
        }
        .preview-icon-row {
            display: inline-flex; align-items: center; gap: 0.5rem;
            margin-inline-start: 0.5rem;
            color: var(--ink-mute);
        }
        .preview-icon-row .icon-btn {
            width: 30px; height: 30px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 6px;
            cursor: pointer; transition: background 0.15s;
        }
        .preview-icon-row .icon-btn:hover { background: var(--surface); color: var(--ink); }

        .preview-cols {
            display: grid; grid-template-columns: 1fr 1fr;
            flex: 1;
        }
        .preview-col {
            padding: 1.4rem 1.4rem 1rem;
        }
        .preview-col + .preview-col {
            border-inline-start: 1px solid var(--hairline-soft);
        }
        .preview-col h5 {
            font-size: 12.5px; font-weight: 500;
            color: var(--ink-mute);
            margin: 0 0 0.85rem;
        }
        .preview-col.ar h5 { font-family: var(--f-arabic); font-size: 14px; }
        .preview-col p {
            margin: 0;
            font-size: 14px; line-height: 1.7;
            color: var(--ink);
        }
        .preview-col.ar p {
            font-family: var(--f-arabic);
            font-size: 15.5px; line-height: 2;
            font-weight: 400;
        }
        .preview-col h4 {
            font-size: 15px; font-weight: 600;
            margin: 0 0 0.7rem;
            color: var(--ink);
        }
        .preview-col.ar h4 { font-family: var(--f-arabic); font-size: 17px; font-weight: 700; }

        .lock {
            background: #FEF3C7; color: #92400E;
            padding: 0.05em 0.35em; border-radius: 4px;
            font-weight: 500;
        }
        html.dark .lock { background: rgba(251, 191, 36, 0.15); color: #FCD34D; }

        .article-chip {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.55rem 0.85rem;
            background: var(--surface);
            border: 1px solid var(--hairline-soft);
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 12.5px;
        }
        .article-chip .a-num { font-weight: 600; color: var(--ink); }
        .article-chip .a-src { color: var(--ink-mute); font-family: var(--f-mono); font-size: 11px; }

        .glossary-strip {
            border-top: 1px solid var(--hairline-soft);
            padding: 1rem 1.4rem;
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 1rem;
            background: var(--surface);
        }
        .glossary-strip .lock-icon {
            width: 32px; height: 32px;
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            color: var(--navy);
        }
        .glossary-strip .label-block .lbl {
            font-size: 13px; font-weight: 600; color: var(--ink); margin: 0;
        }
        .glossary-strip .label-block .sub {
            font-size: 11.5px; color: var(--ink-mute); margin: 0; margin-top: 1px;
        }
        .glossary-strip .term-block {
            display: flex; gap: 1.5rem; align-items: center;
            font-size: 13px;
        }
        .glossary-strip .term-block .ar { font-family: var(--f-arabic); font-size: 15px; }
        .glossary-strip .locked-chip {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.35rem 0.65rem;
            background: var(--green-soft); color: var(--green);
            border-radius: 9999px;
            font-family: var(--f-mono); font-size: 11px; font-weight: 600;
        }

        /* ────────  Stats strip  ──────── */
        .stats {
            background: var(--surface);
            border-top: 1px solid var(--hairline);
            border-bottom: 1px solid var(--hairline);
            padding: 1.5rem 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr) auto;
            gap: 2rem;
            align-items: center;
        }
        @media (max-width: 880px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .stats-grid > .stats-meta { grid-column: 1 / -1; text-align: center; }
        }
        .stat {
            display: flex; align-items: center; gap: 0.85rem;
        }
        .stat-icon {
            width: 36px; height: 36px;
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 8px;
            color: var(--navy);
            display: inline-flex; align-items: center; justify-content: center;
        }
        .stat-num {
            font-family: var(--f-serif);
            font-size: 28px; font-weight: 600;
            letter-spacing: -0.02em;
            color: var(--ink);
            line-height: 1;
        }
        :where(html[dir="rtl"]) .stat-num { font-family: var(--f-arabic); }
        .stat-lbl {
            font-size: 12.5px; color: var(--ink-soft);
            margin-top: 4px;
        }
        .stats-meta {
            text-align: end;
            font-size: 11.5px; color: var(--ink-mute);
            line-height: 1.5;
            font-family: var(--f-mono);
        }

        /* ────────  Capabilities  ──────── */
        .caps {
            padding: clamp(3.5rem, 6vw, 5.5rem) 0 clamp(3rem, 5vw, 5rem);
        }
        .caps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2.25rem;
        }
        .cap .cap-head {
            display: flex; align-items: center; gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .cap .cap-num {
            width: 30px; height: 30px;
            background: var(--navy); color: #fff;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-family: var(--f-mono);
            font-size: 12px; font-weight: 700;
        }
        .cap .cap-icon {
            width: 36px; height: 36px;
            background: var(--surface);
            border: 1px solid var(--hairline);
            border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            color: var(--navy);
        }
        .cap h3 {
            font-size: 17px; font-weight: 600;
            margin: 0 0 0.5rem;
            letter-spacing: -0.01em;
        }
        .cap p {
            margin: 0;
            font-size: 14px; line-height: 1.65;
            color: var(--ink-soft);
            max-width: 28em;
        }

        /* ────────  Bilingual showcase  ──────── */
        .biling {
            margin: clamp(3rem, 5vw, 4rem) 0;
        }
        .biling-card {
            display: grid;
            grid-template-columns: 1fr;
            border-radius: 16px;
            overflow: hidden;
            background: var(--canvas);
            border: 1px solid var(--hairline);
        }
        @media (min-width: 880px) {
            .biling-card { grid-template-columns: 0.9fr 1.4fr; }
        }
        .biling-left {
            background: var(--navy);
            color: #fff;
            padding: 2.5rem 2.25rem;
        }
        .biling-left h3 {
            font-family: var(--f-serif);
            font-weight: 500;
            font-size: clamp(28px, 3.5vw, 38px);
            line-height: 1.12;
            letter-spacing: -0.02em;
            margin: 0.85rem 0 1.25rem;
        }
        :where(html[dir="rtl"]) .biling-left h3 { font-family: var(--f-arabic); font-weight: 700; }
        .biling-left p {
            color: rgba(255,255,255,0.88);
            font-size: 14.5px;
            line-height: 1.65;
            max-width: 26em;
            margin: 0 0 1.5rem;
        }
        .biling-left .eyebrow { color: rgba(255,255,255,0.82); }
        .biling-left .btn-link { color: rgba(255,255,255,0.92); }
        .biling-left .btn-link:hover { color: #fff; }

        .biling-right {
            padding: 2.5rem 2.25rem;
            display: flex; flex-direction: column; gap: 1.5rem;
        }
        .biling-right h4 {
            font-size: 12.5px; font-weight: 500;
            color: var(--ink-mute);
            margin: 0 0 0.85rem;
        }
        .biling-right .clause-ar {
            font-family: var(--f-arabic);
            font-size: 16.5px;
            line-height: 2;
            margin: 0;
            color: var(--ink);
        }
        .biling-right .clause-en p {
            font-size: 14.5px;
            line-height: 1.7;
            margin: 0;
            color: var(--ink-soft);
        }
        .biling-right .clause-en p strong {
            color: var(--ink);
        }
        .lock-row {
            display: grid;
            grid-template-columns: auto 1fr 1fr 1.5fr auto;
            align-items: center;
            gap: 1rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--hairline-soft);
            font-size: 12.5px;
        }
        @media (max-width: 700px) {
            .lock-row { grid-template-columns: 1fr 1fr; gap: 0.5rem 1rem; }
            .lock-row > *:last-child { grid-column: 1 / -1; }
        }
        .lock-row .l-tag { color: var(--ink-mute); font-family: var(--f-mono); font-size: 11px; }
        .lock-row .l-ar { font-family: var(--f-arabic); font-size: 15px; color: var(--ink); }
        .lock-row .l-en { color: var(--ink); font-weight: 500; }
        .lock-row .l-src { color: var(--ink-mute); font-family: var(--f-mono); font-size: 11px; }
        .lock-row .l-status {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.35rem 0.7rem;
            background: var(--green-soft); color: var(--green);
            border-radius: 9999px;
            font-family: var(--f-mono); font-size: 11px; font-weight: 600;
        }

        /* ────────  Coverage / Jurisdictions  ──────── */
        .coverage {
            padding: clamp(3.5rem, 6vw, 5.5rem) 0 clamp(3rem, 5vw, 4.5rem);
        }
        .coverage-grid {
            display: grid;
            grid-template-columns: minmax(180px, 0.8fr) 1fr;
            gap: 3rem;
            align-items: start;
        }
        @media (max-width: 880px) {
            .coverage-grid { grid-template-columns: 1fr; gap: 2rem; }
        }
        .coverage h2 {
            font-family: var(--f-serif);
            font-size: clamp(28px, 3.6vw, 40px);
            font-weight: 500;
            letter-spacing: -0.02em;
            line-height: 1.12;
            margin: 0.75rem 0 0;
        }
        :where(html[dir="rtl"]) .coverage h2 { font-family: var(--f-arabic); font-weight: 700; }
        .jur-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.85rem;
        }
        .jur-card {
            display: block;
            padding: 1.1rem 1.25rem;
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 12px;
            transition: border-color 0.18s, transform 0.18s, box-shadow 0.18s;
        }
        .jur-card:hover {
            border-color: var(--navy);
            transform: translateY(-2px);
            box-shadow: 0 8px 22px -10px rgba(15, 27, 45, 0.18);
        }
        .jur-card .jur-head {
            display: flex; align-items: center; gap: 0.6rem;
            margin-bottom: 0.85rem;
        }
        .jur-card .flag { font-size: 18px; line-height: 1; }
        .jur-card .nm { font-weight: 600; font-size: 14.5px; color: var(--ink); }
        .jur-card .nm.ar { font-family: var(--f-arabic); font-size: 15.5px; font-weight: 700; }
        .jur-card .iso { margin-inline-start: auto; font-family: var(--f-mono); font-size: 10.5px; color: var(--ink-mute); letter-spacing: 0.05em; }
        .jur-card .ref-line { font-size: 12.5px; color: var(--ink-soft); margin: 0; line-height: 1.4; }
        .jur-card .ref-cite { font-family: var(--f-mono); font-size: 11px; color: var(--ink-mute); margin-top: 4px; }
        .jur-card.ref-cite-ar { font-family: var(--f-arabic); }

        /* ────────  How it works  ──────── */
        .how {
            padding: clamp(3.5rem, 6vw, 5.5rem) 0 clamp(3rem, 5vw, 5rem);
            background: var(--surface);
            border-top: 1px solid var(--hairline);
            border-bottom: 1px solid var(--hairline);
        }
        .how-grid {
            display: grid;
            grid-template-columns: minmax(220px, 0.8fr) 1fr;
            gap: 3rem;
            align-items: start;
        }
        @media (max-width: 880px) {
            .how-grid { grid-template-columns: 1fr; gap: 2rem; }
        }
        .how h2 {
            font-family: var(--f-serif);
            font-size: clamp(26px, 3.4vw, 36px);
            font-weight: 500;
            letter-spacing: -0.02em;
            line-height: 1.15;
            margin: 0.75rem 0 0;
        }
        :where(html[dir="rtl"]) .how h2 { font-family: var(--f-arabic); font-weight: 700; }
        .steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            position: relative;
        }
        @media (max-width: 880px) {
            .steps { grid-template-columns: repeat(2, 1fr); gap: 2rem 1rem; }
        }
        @media (max-width: 540px) {
            .steps { grid-template-columns: 1fr; gap: 1.75rem; }
        }
        .step {
            position: relative;
            padding-inline-end: 1.25rem;
        }
        .step .step-icon {
            width: 52px; height: 52px;
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            color: var(--navy);
            position: relative;
            z-index: 2;
        }
        .step .connector {
            position: absolute;
            top: 26px;
            inset-inline-start: 56px;
            inset-inline-end: 0;
            border-top: 2px dotted var(--hairline);
            z-index: 1;
        }
        .step:last-child .connector { display: none; }
        .step h4 {
            font-size: 15.5px; font-weight: 600;
            margin: 1rem 0 0.4rem;
            letter-spacing: -0.01em;
            display: flex; align-items: baseline; gap: 0.4rem;
        }
        .step h4 .step-num {
            font-family: var(--f-mono);
            font-weight: 600;
            color: var(--navy);
        }
        .step p {
            font-size: 13px; line-height: 1.6;
            color: var(--ink-soft);
            margin: 0;
            max-width: 22em;
        }
        @media (max-width: 880px) {
            .step .connector { display: none; }
        }

        /* ────────  Pull quote  ──────── */
        .quote-section {
            padding: clamp(3rem, 5vw, 4.5rem) 0;
        }
        .quote-card {
            background: var(--gold);
            border: 1px solid #EFE2BC;
            border-radius: 16px;
            padding: 2.25rem 2.5rem;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 1.75rem;
            align-items: start;
        }
        html.dark .quote-card {
            border-color: rgba(201, 168, 74, 0.20);
        }
        .quote-card .qmark {
            font-family: var(--f-serif);
            font-size: 86px;
            line-height: 0.7;
            color: var(--gold-deep);
            font-weight: 600;
            margin-top: -0.1em;
        }
        .quote-card h3 {
            font-family: var(--f-serif);
            font-size: 21px; font-weight: 600;
            letter-spacing: -0.015em;
            margin: 0 0 0.85rem;
            color: var(--ink);
        }
        :where(html[dir="rtl"]) .quote-card h3 { font-family: var(--f-arabic); font-weight: 700; }
        .quote-card p {
            margin: 0;
            font-size: 16px;
            line-height: 1.7;
            color: var(--ink);
            max-width: 56em;
        }
        :where(html[dir="rtl"]) .quote-card p { font-size: 17px; line-height: 1.85; }

        @media (max-width: 600px) {
            .quote-card { grid-template-columns: 1fr; gap: 0.5rem; padding: 2rem 1.5rem; }
            .quote-card .qmark { font-size: 64px; }
        }

        /* ────────  Final CTA  ──────── */
        .final-cta {
            padding: clamp(3rem, 5vw, 4rem) 0 clamp(3.5rem, 6vw, 5rem);
            border-top: 1px solid var(--hairline);
        }
        .final-cta-card {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
            gap: 2rem;
        }
        .final-cta-card .copy h3 {
            font-family: var(--f-serif);
            font-size: clamp(24px, 3vw, 32px);
            font-weight: 500;
            letter-spacing: -0.018em;
            margin: 0 0 0.5rem;
            color: var(--ink);
        }
        :where(html[dir="rtl"]) .final-cta-card .copy h3 { font-family: var(--f-arabic); font-weight: 700; }
        .final-cta-card .copy p {
            margin: 0;
            font-size: 14.5px;
            color: var(--ink-soft);
            max-width: 36em;
        }

        /* ────────  Footer  ──────── */
        .footer {
            background: var(--navy);
            color: rgba(255,255,255,0.86);
            padding: clamp(3rem, 5vw, 4.5rem) 0 1.75rem;
        }
        .footer .logo-text { color: #fff; }
        .footer .logo-mark {
            background: rgba(255,255,255,0.10);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.12);
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr 1fr;
            gap: 3rem;
        }
        @media (max-width: 880px) {
            .footer-grid { grid-template-columns: 1fr 1fr; gap: 2rem; }
        }
        @media (max-width: 540px) {
            .footer-grid { grid-template-columns: 1fr; }
        }
        .footer h5 {
            font-size: 12.5px;
            font-weight: 600;
            color: #fff;
            margin: 0 0 1rem;
            letter-spacing: -0.005em;
        }
        .footer ul li { margin-bottom: 0.55rem; }
        .footer ul a {
            font-size: 13.5px;
            color: rgba(255,255,255,0.78);
            transition: color 0.15s;
        }
        .footer ul a:hover { color: #fff; }
        .footer .blurb {
            font-size: 13.5px;
            color: rgba(255,255,255,0.78);
            line-height: 1.6;
            margin-top: 1rem;
            max-width: 22em;
        }
        .footer .socials {
            display: flex; gap: 0.5rem;
            margin-top: 1.25rem;
        }
        .footer .socials a {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: rgba(255,255,255,0.10);
            color: rgba(255,255,255,0.85);
            display: inline-flex; align-items: center; justify-content: center;
            transition: background 0.15s, color 0.15s;
        }
        .footer .socials a:hover { background: rgba(255,255,255,0.18); color: #fff; }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.12);
            margin-top: 3rem;
            padding-top: 1.5rem;
            font-size: 11.5px;
            color: rgba(255,255,255,0.72);
            display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
            font-family: var(--f-mono);
        }

        /* ────────  Animation  ──────── */
        .rise { opacity: 0; transform: translateY(8px); animation: rise 0.6s cubic-bezier(0.2, 0.7, 0.2, 1) forwards; }
        .r-1 { animation-delay: 0.04s; }
        .r-2 { animation-delay: 0.12s; }
        .r-3 { animation-delay: 0.20s; }
        .r-4 { animation-delay: 0.30s; }
        @keyframes rise { to { opacity: 1; transform: translateY(0); } }
        @media (prefers-reduced-motion: reduce) {
            .rise { opacity: 1; transform: none; animation: none; }
        }
    </style>
</head>
<body>

{{-- ────────────────────  Header  ──────────────────── --}}
<header class="nav-shell">
    <div class="wrap" style="display: flex; align-items: center; justify-content: space-between; height: 72px; gap: 1rem;">
        <a href="{{ route('home') }}" style="display: inline-flex; align-items: center; gap: 0.65rem;" aria-label="My-lawyer home">
            <span class="logo-mark" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18"/>
                    <path d="M5 21V10l7-4 7 4v11"/>
                    <path d="M9 21v-7h6v7"/>
                    <circle cx="12" cy="10.5" r="1" fill="currentColor"/>
                </svg>
            </span>
            <span class="logo-text">My-lawyer</span>
        </a>

        <nav style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('marketing.glossary') }}" class="nav-link" style="display: none;" data-show-md>{{ __('Glossary') }}</a>
            <a href="{{ route('marketing.faq') }}" class="nav-link" style="display: none;" data-show-md>{{ __('FAQ') }}</a>
            <a href="{{ route('home') }}#preview" class="nav-link" style="display: none;" data-show-md>{{ __('Sample output') }}</a>
        </nav>

        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <form method="POST" action="{{ route('locale.set') }}" class="lang-toggle" id="lang-form">
                @csrf
                <button type="button" class="{{ ! $isAr ? 'active' : '' }}" data-lang="en">EN</button>
                <button type="button" class="{{ $isAr ? 'active' : '' }}" data-lang="ar">AR</button>
                <input type="hidden" name="locale" id="lang-input" value="{{ $isAr ? 'en' : 'ar' }}">
            </form>

            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">{{ __('Dashboard') }}</a>
            @endauth
            @guest
                <a href="{{ route('login') }}" class="btn-outline" style="padding: 0.6rem 1rem;">{{ __('Sign in') }}</a>
                <a href="{{ route('register') }}" class="btn-primary" style="padding: 0.65rem 1.1rem;">{{ __('Start free') }}</a>
            @endguest
        </div>
    </div>

    <style>
        @media (min-width: 880px) {
            [data-show-md] { display: inline-block !important; }
        }
    </style>
</header>

<script>
(function() {
    const form = document.getElementById('lang-form');
    if (!form) return;
    const input = document.getElementById('lang-input');
    form.querySelectorAll('button[data-lang]').forEach(btn => {
        btn.addEventListener('click', () => {
            input.value = btn.dataset.lang;
            form.submit();
        });
    });
})();
</script>

{{-- ────────────────────  Hero  ──────────────────── --}}
<section class="hero">
    <div class="wrap hero-grid">
        <div>
            <span class="badge-tag rise r-1" aria-hidden="false">
                <span class="pip"></span>{{ __('AI legal drafting · built in Cairo') }}
            </span>
            <h1 class="display-serif rise r-2">
                @if ($isAr)
                    صياغة عقود <span style="color: var(--navy);">القانون المصري</span>، بدقة المحامي وسرعة الذكاء الاصطناعي.
                @else
                    Draft contracts under <span style="color: var(--navy);">Egyptian law</span>, with the rigor of a senior associate.
                @endif
            </h1>
            <p class="lede rise r-3">
                @if ($isAr)
                    صياغة قانونية تبدأ بالعربية، مرتبطة بالنصوص الفعلية للقانون المدني المصري وقوانين الشركات والعمل والأسواق. كل بند موثَّق، كل مصطلح مقفل. تصدير ثنائي اللغة جنباً إلى جنب. توسعنا الإقليمي قيد التطوير.
                @else
                    Arabic-first legal drafting grounded in the actual text of the Egyptian Civil Code and adjacent statutes. Every clause cited, every term locked, exported bilingually. Regional expansion in active development.
                @endif
            </p>
            <div class="hero-cta-row rise r-3">
                @guest
                    <a href="{{ route('register') }}" class="btn-primary">
                        {{ __('Start drafting for free') }}
                    </a>
                @endguest
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary">
                        {{ __('Open dashboard') }}
                    </a>
                @endauth
                <a href="#preview" class="btn-outline">
                    {{ __('View sample output') }}
                </a>
            </div>
            <div class="hero-foot rise r-3">
                <span class="check">✓</span>
                {{ __('No credit card required. Start in seconds.') }}
            </div>
        </div>

        {{-- Product preview --}}
        <div id="preview" class="preview rise r-4">
            <aside class="preview-side">
                {{-- Document --}}
                <span class="ic active" title="Drafts" aria-label="Drafts">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="14 3 14 9 20 9"/></svg>
                </span>
                {{-- Search --}}
                <span class="ic" aria-label="Search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                </span>
                {{-- Library --}}
                <span class="ic" aria-label="Library">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 7h20"/><path d="M5 7v13"/><path d="M19 7v13"/><path d="M9 11h6"/><path d="M9 15h6"/></svg>
                </span>
                {{-- History --}}
                <span class="ic" aria-label="History">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>
                </span>
                {{-- Settings --}}
                <span class="ic" aria-label="Settings">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h0a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h0a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v0a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                </span>
            </aside>

            <div class="preview-main">
                <div class="preview-bar">
                    <span class="crumb">Drafts</span>
                    <span class="arrow-sep">{{ $isAr ? '←' : '›' }}</span>
                    <span class="crumb-cur">MoU – UAE</span>
                    <span class="arrow-sep">{{ $isAr ? '←' : '›' }}</span>
                    <span class="crumb">v2</span>

                    <span class="verified-chip">
                        <span class="v-tick">✓</span>
                        8 / 8 {{ __('verified') }}
                    </span>

                    <span class="preview-icon-row">
                        <span class="icon-btn" aria-label="Share">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
                        </span>
                        <span class="icon-btn" aria-label="Download">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        </span>
                        <span class="icon-btn" aria-label="More">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="12" r="1.2"/><circle cx="12" cy="12" r="1.2"/><circle cx="19" cy="12" r="1.2"/></svg>
                        </span>
                    </span>
                </div>

                <div class="preview-cols" style="flex: 1;">
                    <div class="preview-col ar" dir="rtl">
                        <h5>العربية</h5>
                        <h4>٨. حسن النية</h4>
                        <p>
                            يتعهد الطرفان بتنفيذ هذا الاتفاق بـ<span class="lock">حسن نية</span> والامتناع عن أي تصرف من شأنه الإضرار بمقصد هذا الاتفاق أو تحقيق نتيجة مخالفة له.
                        </p>
                        <span class="article-chip">
                            <span class="a-num">المادة ٢٤٦</span>
                            <span class="a-src">UAE Civil Code</span>
                        </span>
                    </div>
                    <div class="preview-col en" dir="ltr">
                        <h5>English</h5>
                        <h4>8. Good Faith</h4>
                        <p>
                            The Parties shall perform this Agreement in good faith and refrain from any act that may undermine its purpose or defeat its provisions.
                        </p>
                        <span class="article-chip">
                            <span class="a-num">Article 246</span>
                            <span class="a-src">UAE Civil Code</span>
                        </span>
                    </div>
                </div>

                <div class="glossary-strip">
                    <span class="lock-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <div class="label-block">
                        <p class="lbl">{{ __('Glossary lock') }}</p>
                        <p class="sub">{{ __('Term locked across the document') }}</p>
                    </div>
                    <div class="term-block">
                        <span class="ar">حسن نية</span>
                        <span style="color: var(--ink-mute);">{{ $isAr ? '←' : '→' }}</span>
                        <span>good faith</span>
                    </div>
                    <span class="locked-chip">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Locked
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ────────────────────  Stats strip  ──────────────────── --}}
<section class="stats">
    <div class="wrap stats-grid">
        <div class="stat">
            <span class="stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="14 3 14 9 20 9"/></svg>
            </span>
            <div>
                <div class="stat-num">{{ number_format($displayDocs) }}<span style="color: var(--ink-mute);">+</span></div>
                <div class="stat-lbl">{{ __('Documents indexed') }}</div>
            </div>
        </div>
        <div class="stat">
            <span class="stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15 15 0 0 1 4 10 15 15 0 0 1-4 10 15 15 0 0 1-4-10 15 15 0 0 1 4-10z"/></svg>
            </span>
            <div>
                <div class="stat-num">{{ $displayJurs }}</div>
                <div class="stat-lbl">{{ __('MENA jurisdictions') }}</div>
            </div>
        </div>
        <div class="stat">
            <span class="stat-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <div>
                <div class="stat-num">700<span style="color: var(--ink-mute);">+</span></div>
                <div class="stat-lbl">{{ __('Legal terms locked') }}</div>
            </div>
        </div>
        <div class="stats-meta">
            <div>{{ __('Live counts from our database') }}</div>
            <div>{{ __('Last updated') }}: {{ $lastUpdated }}</div>
        </div>
    </div>
</section>

{{-- ────────────────────  Capabilities  ──────────────────── --}}
<section class="caps">
    <div class="wrap">
        <div class="caps-grid">
            <div class="cap">
                <div class="cap-head">
                    <span class="cap-num">1</span>
                    <span class="cap-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </span>
                </div>
                <h3>{{ __('Citation-grounded') }}</h3>
                <p>{{ __('Every clause is checked against the real text of statutes and regulations. Unverifiable citations are flagged, not shipped.') }}</p>
            </div>
            <div class="cap">
                <div class="cap-head">
                    <span class="cap-num">2</span>
                    <span class="cap-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="m22 22-5-10-5 10"/><path d="M14 18h6"/></svg>
                    </span>
                </div>
                <h3>{{ __('Bilingual by default') }}</h3>
                <p>{{ __('Draft in Arabic and English side-by-side with a glossary lock that keeps legal meaning consistent, document-wide.') }}</p>
            </div>
            <div class="cap">
                <div class="cap-head">
                    <span class="cap-num">3</span>
                    <span class="cap-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 22h18"/><path d="M5 22V11l7-4 7 4v11"/><path d="M9 22V14h6v8"/></svg>
                    </span>
                </div>
                <h3>{{ __('Region-built') }}</h3>
                <p>{{ __('Built for 11 jurisdictions with their own laws, citation formats, and institutional vocabulary — not generic templates.') }}</p>
            </div>
            <div class="cap">
                <div class="cap-head">
                    <span class="cap-num">4</span>
                    <span class="cap-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>
                    </span>
                </div>
                <h3>{{ __('Versioned & auditable') }}</h3>
                <p>{{ __('Every revision is captured. Compare changes, review history, and export with confidence.') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- ────────────────────  Bilingual showcase  ──────────────────── --}}
<section class="biling">
    <div class="wrap">
        <div class="biling-card">
            <div class="biling-left">
                <span class="eyebrow">{{ __('Bilingual showcase') }}</span>
                <h3>
                    @if ($isAr)
                        المصطلحات مقفلة. المعنى محفوظ.
                    @else
                        Terminology locked.<br>Meaning preserved.
                    @endif
                </h3>
                <p>
                    {{ __('Our glossary lock ensures civil-law concepts render identically every time, across the entire document.') }}
                </p>
                <a href="{{ route('marketing.glossary') }}" class="btn-link">
                    {{ __('Explore the glossary') }}
                    <span aria-hidden="true">{{ $isAr ? '←' : '→' }}</span>
                </a>
            </div>
            <div class="biling-right">
                <div class="clause-ar-block">
                    <h4>{{ __('UAE MoU – Clause (Arabic)') }}</h4>
                    <p class="clause-ar" dir="rtl">
                        يتعهد الطرفان بالحفاظ على <span class="lock">سرية</span> المعلومات المتبادلة وعدم إفشائها لأي طرف ثالث إلا بموجب متطلب قانوني أو بموافقة خطية مسبقة من الطرف الآخر.
                    </p>
                </div>
                <div class="clause-en">
                    <h4>{{ __('What the glossary lock does (English)') }}</h4>
                    <p>
                        <strong>"سرية"</strong> is always rendered as "confidentiality". Not "secrecy", not "privacy". The legal concept is locked to its civil-law meaning under UAE law (Federal Decree-Law No. 5 of 1985, Civil Transactions Law, Art. 892).
                    </p>
                </div>
                <div class="lock-row">
                    <span class="l-tag">{{ __('Locked term') }}</span>
                    <span class="l-ar">سرية</span>
                    <span class="l-en">confidentiality</span>
                    <span class="l-src">UAE Civil Code – Art. 892</span>
                    <span class="l-status">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Locked
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ────────────────────  Coverage  ──────────────────── --}}
<section class="coverage">
    <div class="wrap">
        <div class="coverage-grid">
            <div>
                <span class="eyebrow">{{ __('Coverage') }}</span>
                <h2>
                    @if ($isAr)
                        ١١ ولاية قضائية. أنظمة قانون مدني.
                    @else
                        11 jurisdictions.<br>Civil-law systems.
                    @endif
                </h2>
                <a href="{{ route('marketing.glossary') }}" class="btn-link" style="margin-top: 1.25rem;">
                    {{ __('View all jurisdictions') }}
                    <span aria-hidden="true">{{ $isAr ? '←' : '→' }}</span>
                </a>
            </div>
            <div class="jur-cards">
                @foreach ($jurisdictions as $j)
                    <a href="{{ route('marketing.jurisdiction', ['iso' => $j['iso']]) }}" class="jur-card">
                        <div class="jur-head">
                            <span class="flag" aria-hidden="true">{{ $j['flag'] }}</span>
                            <span class="nm {{ $isAr ? 'ar' : '' }}">{{ $isAr ? $j['ar'] : $j['en'] }}</span>
                            <span class="iso">{{ $j['iso'] }}</span>
                        </div>
                        <p class="ref-line {{ $isAr ? 'ar' : '' }}" style="{{ $isAr ? 'font-family: var(--f-arabic);' : '' }}">{{ $isAr ? $j['note_ar'] : $j['note_en'] }}</p>
                        <p class="ref-cite {{ $isAr ? 'ar' : '' }}" style="{{ $isAr ? 'font-family: var(--f-arabic);' : '' }}">{{ $isAr ? $j['cite_ar'] : $j['cite_en'] }}</p>
                        <div style="margin-top: 0.85rem;">
                            <x-coverage-badge :iso="$j['iso']" />
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ────────────────────  How it works  ──────────────────── --}}
<section class="how">
    <div class="wrap">
        <div class="how-grid">
            <div>
                <span class="eyebrow">{{ __('How it works') }}</span>
                <h2>
                    @if ($isAr)
                        من ملخّص إلى عقد ثنائي اللغة في أربع خطوات.
                    @else
                        From brief to<br>bilingual contract<br>in four steps.
                    @endif
                </h2>
            </div>
            <div class="steps">
                <?php
                    $steps = [
                        [
                            'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="14 3 14 9 20 9"/><path d="M9 14h6"/><path d="M9 18h4"/></svg>',
                            'title_en' => 'Brief',
                            'title_ar' => 'ملخّص',
                            'body_en' => 'Upload your brief or answer a few questions about the deal and jurisdiction.',
                            'body_ar' => 'حمّل الملخّص أو أجب عن أسئلة موجزة حول الصفقة والولاية القضائية.',
                        ],
                        [
                            'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
                            'title_en' => 'Draft in Arabic',
                            'title_ar' => 'الصياغة بالعربية',
                            'body_en' => 'We draft the contract in Arabic using jurisdiction-specific language and structure.',
                            'body_ar' => 'نصوغ العقد بالعربية بلغة وبنية مناسبة لكل ولاية قضائية.',
                        ],
                        [
                            'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>',
                            'title_en' => 'Citations verified',
                            'title_ar' => 'توثيق الاستشهادات',
                            'body_en' => 'Every clause is matched to the controlling statute. 100% verifiable or it gets flagged.',
                            'body_ar' => 'تتم مطابقة كل بند بالنص الفعلي للقانون الحاكم. التوثيق ١٠٠٪ أو علامة تحذير.',
                        ],
                        [
                            'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
                            'title_en' => 'Bilingual export',
                            'title_ar' => 'تصدير ثنائي اللغة',
                            'body_en' => 'Export Arabic and English side-by-side with citations, ready to use.',
                            'body_ar' => 'تصدير العربية والإنجليزية جنباً إلى جنب مع الاستشهادات، جاهز للاستخدام.',
                        ],
                    ];
                ?>
                @foreach ($steps as $i => $step)
                    <div class="step">
                        <span class="step-icon">{!! $step['icon'] !!}</span>
                        <span class="connector"></span>
                        <h4><span class="step-num">{{ $i + 1 }}.</span> {{ $isAr ? $step['title_ar'] : $step['title_en'] }}</h4>
                        <p>{{ $isAr ? $step['body_ar'] : $step['body_en'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ────────────────────  Pull quote  ──────────────────── --}}
<section class="quote-section">
    <div class="wrap">
        <div class="quote-card">
            <span class="qmark" aria-hidden="true">"</span>
            <div>
                <h3>{{ __('Why we built My-lawyer') }}</h3>
                <p>
                    @if ($isAr)
                        هناك أدوات قانونية كثيرة تترجم اللغة. بنينا My-lawyer ليترجم المعنى القانوني — بأمانة، باتساق، وباحترام كامل لأنظمة القانون المدني في منطقتنا.
                    @else
                        Too many legal tools translate language. We built My-lawyer to translate legal meaning — faithfully, consistently, and with full respect for the civil-law systems of our region.
                    @endif
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ────────────────────  How we work — honest trust  ──────────────────── --}}
<section style="background: var(--surface); border-top: 1px solid var(--hairline); border-bottom: 1px solid var(--hairline); padding: clamp(3.5rem, 6vw, 5rem) 0;">
    <div class="wrap">
        <div class="head-block" style="margin-bottom: 2.5rem; max-width: 38rem;">
            <span class="eyebrow">
                <span style="display:inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--navy);"></span>
                {{ __('How we work') }}
            </span>
            <h2 style="font-family: var(--f-serif); font-size: clamp(28px, 3.6vw, 40px); font-weight: 500; letter-spacing: -0.02em; line-height: 1.12; margin: 0.75rem 0 0;">
                @if ($isAr)
                    صدقاً عن ما نفعله، وما لا نفعله.
                @else
                    Honestly, what we do — and what we don't.
                @endif
            </h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">

            {{-- What we do --}}
            <div class="card-base" style="padding: 1.5rem 1.6rem; background: var(--canvas);">
                <h3 style="font-family: var(--f-serif); font-size: 18px; font-weight: 600; color: var(--ink); margin: 0 0 1rem; letter-spacing: -0.01em;">
                    {{ __('What we do') }}
                </h3>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                    <li style="display: flex; gap: 0.65rem; font-size: 14px; line-height: 1.6; color: var(--ink-soft);">
                        <span style="color: var(--green); flex-shrink: 0;">✓</span>
                        <span>{{ __('Index the actual text of Egyptian Civil Code, Companies Law 159/1981, Labour Law 12/2003, and adjacent statutes.') }}</span>
                    </li>
                    <li style="display: flex; gap: 0.65rem; font-size: 14px; line-height: 1.6; color: var(--ink-soft);">
                        <span style="color: var(--green); flex-shrink: 0;">✓</span>
                        <span>{{ __('Cite the controlling article for every clause we generate.') }}</span>
                    </li>
                    <li style="display: flex; gap: 0.65rem; font-size: 14px; line-height: 1.6; color: var(--ink-soft);">
                        <span style="color: var(--green); flex-shrink: 0;">✓</span>
                        <span>{{ __('Lock civil-law terminology so the same concept is translated identically across the whole document.') }}</span>
                    </li>
                    <li style="display: flex; gap: 0.65rem; font-size: 14px; line-height: 1.6; color: var(--ink-soft);">
                        <span style="color: var(--green); flex-shrink: 0;">✓</span>
                        <span>{{ __('Refresh statutes against the source nightly and flag stale citations.') }}</span>
                    </li>
                    <li style="display: flex; gap: 0.65rem; font-size: 14px; line-height: 1.6; color: var(--ink-soft);">
                        <span style="color: var(--green); flex-shrink: 0;">✓</span>
                        <span>{{ __('Save every revision so you can roll back when senior counsel disagrees.') }}</span>
                    </li>
                </ul>
            </div>

            {{-- What we don't --}}
            <div class="card-base" style="padding: 1.5rem 1.6rem; background: var(--canvas);">
                <h3 style="font-family: var(--f-serif); font-size: 18px; font-weight: 600; color: var(--ink); margin: 0 0 1rem; letter-spacing: -0.01em;">
                    {{ __("What we don't") }}
                </h3>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                    <li style="display: flex; gap: 0.65rem; font-size: 14px; line-height: 1.6; color: var(--ink-soft);">
                        <span style="color: var(--ink-mute); flex-shrink: 0;">×</span>
                        <span>{{ __('We are not a substitute for a licensed lawyer. Every clause we generate must be reviewed before signing.') }}</span>
                    </li>
                    <li style="display: flex; gap: 0.65rem; font-size: 14px; line-height: 1.6; color: var(--ink-soft);">
                        <span style="color: var(--ink-mute); flex-shrink: 0;">×</span>
                        <span>{{ __('We do not give legal advice. We draft. The judgment call is yours.') }}</span>
                    </li>
                    <li style="display: flex; gap: 0.65rem; font-size: 14px; line-height: 1.6; color: var(--ink-soft);">
                        <span style="color: var(--ink-mute); flex-shrink: 0;">×</span>
                        <span>{{ __('We do not silently ship clauses with unverifiable citations. We flag them.') }}</span>
                    </li>
                    <li style="display: flex; gap: 0.65rem; font-size: 14px; line-height: 1.6; color: var(--ink-soft);">
                        <span style="color: var(--ink-mute); flex-shrink: 0;">×</span>
                        <span>{{ __('We do not train our upstream AI providers on your contracts. Your content stays yours.') }}</span>
                    </li>
                    <li style="display: flex; gap: 0.65rem; font-size: 14px; line-height: 1.6; color: var(--ink-soft);">
                        <span style="color: var(--ink-mute); flex-shrink: 0;">×</span>
                        <span>{{ __('We do not pretend to cover jurisdictions we have not properly studied. Coverage status is shown on each jurisdiction page.') }}</span>
                    </li>
                </ul>
            </div>

            {{-- Where we are --}}
            <div class="card-base" style="padding: 1.5rem 1.6rem; background: var(--canvas);">
                <h3 style="font-family: var(--f-serif); font-size: 18px; font-weight: 600; color: var(--ink); margin: 0 0 1rem; letter-spacing: -0.01em;">
                    {{ __('Where we stand today') }}
                </h3>
                <p style="font-size: 14px; line-height: 1.65; color: var(--ink-soft); margin: 0 0 1rem;">
                    {{ __('Egypt is our launch jurisdiction. We are working with Egyptian counsel to get the corpus to production-grade citation accuracy. The other ten MENA jurisdictions are in active development with the same standard.') }}
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                    <span style="display: inline-flex; align-items: center; gap: 0.4rem; font-family: var(--f-mono); font-size: 11px; padding: 0.3rem 0.7rem; background: #DDF1E5; color: #0E5A36; border-radius: 4px; font-weight: 600;">
                        🇪🇬 EG · LIVE
                    </span>
                    <span style="font-family: var(--f-mono); font-size: 11px; padding: 0.3rem 0.7rem; background: #FBF1D8; color: #7A5A0F; border-radius: 4px; font-weight: 600;">
                        🇸🇦 SA · BETA
                    </span>
                    <span style="font-family: var(--f-mono); font-size: 11px; padding: 0.3rem 0.7rem; background: #FBF1D8; color: #7A5A0F; border-radius: 4px; font-weight: 600;">
                        🇦🇪 AE · BETA
                    </span>
                    <span style="font-family: var(--f-mono); font-size: 11px; padding: 0.3rem 0.7rem; background: #E9EDF2; color: #4B5868; border-radius: 4px; font-weight: 600;">
                        +8 PREVIEW
                    </span>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ────────────────────  Final CTA  ──────────────────── --}}
<section class="final-cta">
    <div class="wrap final-cta-card">
        <div class="copy">
            <h3>{{ __('Ready to draft with confidence?') }}</h3>
            <p>{{ __('Built in Cairo, for Egyptian corporate counsel — and for the region as we expand.') }}</p>
        </div>
        <div style="display: flex; gap: 0.85rem; flex-wrap: wrap; align-items: center;">
            @guest
                <a href="{{ route('register') }}" class="btn-primary">{{ __('Start drafting for free') }}</a>
                <a href="#preview" class="btn-outline">{{ __('View sample output') }}</a>
            @endguest
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">{{ __('Open dashboard') }}</a>
            @endauth
        </div>
    </div>
</section>

{{-- ────────────────────  Footer  ──────────────────── --}}
<footer class="footer">
    <div class="wrap footer-grid">
        <div>
            <a href="{{ route('home') }}" style="display: inline-flex; align-items: center; gap: 0.65rem;">
                <span class="logo-mark" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18"/>
                        <path d="M5 21V10l7-4 7 4v11"/>
                        <path d="M9 21v-7h6v7"/>
                        <circle cx="12" cy="10.5" r="1" fill="currentColor"/>
                    </svg>
                </span>
                <span class="logo-text">My-lawyer</span>
            </a>
            <p class="blurb">{{ __('Built in Cairo for the region.') }}</p>
            <div class="socials">
                <a href="#" aria-label="LinkedIn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zM8.5 18.5v-8H6v8zM7.25 9.25a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zM18.5 18.5v-4.6c0-2.4-1.3-3.5-3-3.5a2.7 2.7 0 0 0-2.5 1.4v-1.3H10.5v8H13v-4.4c0-1 .7-1.6 1.5-1.6s1.5.6 1.5 1.6v4.4z"/></svg>
                </a>
                <a href="#" aria-label="X">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                <a href="#" aria-label="YouTube">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M23 7.6c0-2-1.6-3.6-3.6-3.6H4.6C2.6 4 1 5.6 1 7.6v8.8c0 2 1.6 3.6 3.6 3.6h14.8c2 0 3.6-1.6 3.6-3.6zM10 16V8l6 4z"/></svg>
                </a>
            </div>
        </div>
        <div>
            <h5>{{ __('Jurisdictions') }}</h5>
            <ul>
                @foreach (array_slice($jurisdictions, 0, 6) as $j)
                    <li><a href="{{ route('marketing.jurisdiction', ['iso' => $j['iso']]) }}">{{ $isAr ? $j['ar'] : $j['en'] }}</a></li>
                @endforeach
            </ul>
        </div>
        <div>
            <h5>{{ __('Resources') }}</h5>
            <ul>
                <li><a href="{{ route('marketing.glossary') }}">{{ __('Glossary') }}</a></li>
                <li><a href="{{ route('marketing.faq') }}">{{ __('FAQ') }}</a></li>
                <li><a href="{{ route('home') }}#preview">{{ __('Sample output') }}</a></li>
                <li><a href="{{ route('login') }}">{{ __('Sign in') }}</a></li>
            </ul>
        </div>
        <div>
            <h5>{{ __('Legal') }}</h5>
            <ul>
                <li><a href="{{ route('legal.terms') }}">{{ __('Terms of Service') }}</a></li>
                <li><a href="{{ route('legal.privacy') }}">{{ __('Privacy Policy') }}</a></li>
                <li><a href="{{ route('legal.dpa') }}">{{ __('DPA') }}</a></li>
                <li><a href="{{ route('legal.aup') }}">{{ __('Acceptable Use') }}</a></li>
            </ul>
        </div>
    </div>
    <div class="wrap footer-bottom">
        <span>© {{ date('Y') }} My-lawyer. {{ __('All rights reserved.') }}</span>
        <span>{{ __('Built in Cairo for the region') }}</span>
    </div>
</footer>

@include('partials.cookie-consent')

</body>
</html>
