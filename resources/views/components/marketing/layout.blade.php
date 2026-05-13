@props([
    'pageTitle' => 'My-lawyer · AI legal drafting for MENA jurisdictions',
    'pageDescription' => 'AI contract drafting for MENA corporate counsel. Arabic-first, citation-verified, bilingual export. 11 jurisdictions, civil-law glossary lock.',
    'canonical' => null,
    'jsonLd' => null,
])
@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $dir = $isAr ? 'rtl' : 'ltr';
    $canonical = $canonical ?? url(request()->path());
    $themeClass = request()->cookie('theme') === 'dark' ? 'dark' : '';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}" class="{{ $themeClass }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="robots" content="index, follow, max-image-preview:large">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:locale" content="{{ $isAr ? 'ar_AR' : 'en_US' }}">
    <meta property="og:locale:alternate" content="{{ $isAr ? 'en_US' : 'ar_AR' }}">
    <meta property="og:site_name" content="My-lawyer">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">

    <link rel="alternate" hreflang="ar" href="{{ $canonical }}?lang=ar">
    <link rel="alternate" hreflang="en" href="{{ $canonical }}?lang=en">
    <link rel="alternate" hreflang="x-default" href="{{ $canonical }}">

    @if ($jsonLd)
        <script type="application/ld+json">{!! $jsonLd !!}</script>
    @endif

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600&family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @fonts
    @vite(['resources/css/app.css'])

    <style>
        :root {
            /* Tokens audited for WCAG AA contrast on body text (4.5:1) */
            --canvas: #FFFFFF;
            --surface: #F4F6F9;
            --surface-2: #E9EDF2;
            --ink: #0B1626;          /* 17.4:1 on canvas — AAA */
            --ink-soft: #3F4A5A;     /* 8.6:1 on canvas — AAA */
            --ink-mute: #5C6677;     /* 5.4:1 on canvas — AA body, AAA large */
            --hairline: #DCE2EA;
            --hairline-soft: #E6EAF0;
            --navy: #0F2942;
            --navy-deep: #0A1E33;
            --navy-light: #1B3A6B;
            --navy-soft: #E1E9F6;
            --gold: #FAF3E0;
            --gold-deep: #B8932F;
            --green: #156B40;        /* darker green for chip text on light bg */
            --green-soft: #DDF1E5;

            --f-serif:  'Source Serif 4', 'Source Serif Pro', Georgia, serif;
            --f-sans:   'Inter', system-ui, -apple-system, sans-serif;
            --f-mono:   'JetBrains Mono', ui-monospace, monospace;
            --f-arabic: 'Tajawal', 'Inter', system-ui, sans-serif;
        }

        html.dark {
            --canvas: #0A0F18;
            --surface: #131B2C;
            --surface-2: #1B243A;
            --ink: #F6F8FB;          /* 18:1 on canvas */
            --ink-soft: #C5CDD9;     /* 9.8:1 on canvas — AAA */
            --ink-mute: #98A3B5;     /* 5.5:1 on canvas — AA body */
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
        .wrap-narrow { max-width: 880px; margin: 0 auto; padding: 0 1.5rem; }
        @media (min-width: 768px) { .wrap-narrow { padding: 0 2.5rem; } }

        ::selection { background: var(--navy); color: #fff; }

        /* ────────  Eyebrow / Badge tag  ──────── */
        .eyebrow {
            font-family: var(--f-mono);
            font-size: 11px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--ink-mute);
            font-weight: 500;
            display: inline-flex; align-items: center; gap: 0.45rem;
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
            font-size: 17px; line-height: 1.65;
            max-width: 32em; font-weight: 400;
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
            cursor: pointer; text-decoration: none;
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
            cursor: pointer; text-decoration: none;
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
            background: var(--navy); color: #fff;
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
        .lang-toggle button.active { background: var(--navy); color: #fff; }

        /* ────────  Section primitives  ──────── */
        .section { padding: clamp(3.5rem, 6vw, 5.5rem) 0; }
        .section-tight { padding: clamp(2.5rem, 4vw, 3.5rem) 0; }
        .section-surface { background: var(--surface); border-top: 1px solid var(--hairline); border-bottom: 1px solid var(--hairline); }

        /* ────────  Eyebrow heading combo  ──────── */
        .head-block { max-width: 38rem; }
        .head-block h1 {
            font-family: var(--f-serif);
            font-size: clamp(36px, 5vw, 60px);
            font-weight: 500;
            letter-spacing: -0.022em;
            line-height: 1.06;
            margin: 1rem 0 1.25rem;
            color: var(--ink);
        }
        :where(html[dir="rtl"]) .head-block h1 { font-family: var(--f-arabic); font-weight: 700; line-height: 1.18; }
        .head-block h2 {
            font-family: var(--f-serif);
            font-size: clamp(28px, 3.6vw, 40px);
            font-weight: 500;
            letter-spacing: -0.02em;
            line-height: 1.12;
            margin: 0.75rem 0 0;
        }
        :where(html[dir="rtl"]) .head-block h2 { font-family: var(--f-arabic); font-weight: 700; }
        .head-block .lede { margin-top: 1.5rem; }

        /* ────────  Card primitives  ──────── */
        .card-base {
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 12px;
            transition: border-color 0.18s, transform 0.18s, box-shadow 0.18s;
        }
        .card-hover:hover {
            border-color: var(--navy);
            transform: translateY(-2px);
            box-shadow: 0 8px 22px -10px rgba(15, 27, 45, 0.18);
        }

        /* Jurisdiction-style card */
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

        /* Stat icon block */
        .stat-icon {
            width: 36px; height: 36px;
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 8px;
            color: var(--navy);
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        /* Divider hairline */
        .hairline-y { border-top: 1px solid var(--hairline); border-bottom: 1px solid var(--hairline); }

        /* Lock highlight */
        .lock {
            background: #FEF3C7; color: #92400E;
            padding: 0.05em 0.35em; border-radius: 4px;
            font-weight: 500;
        }
        html.dark .lock { background: rgba(251, 191, 36, 0.15); color: #FCD34D; }

        /* Pull-quote / gold panel */
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
        html.dark .quote-card { border-color: rgba(201, 168, 74, 0.20); }
        .quote-card .qmark {
            font-family: var(--f-serif);
            font-size: 86px; line-height: 0.7;
            color: var(--gold-deep); font-weight: 600;
            margin-top: -0.1em;
        }
        @media (max-width: 600px) {
            .quote-card { grid-template-columns: 1fr; gap: 0.5rem; padding: 2rem 1.5rem; }
            .quote-card .qmark { font-size: 64px; }
        }

        /* ────────  Footer  ──────── */
        .footer {
            background: var(--navy);
            color: rgba(255,255,255,0.86);
            padding: clamp(3rem, 5vw, 4.5rem) 0 1.75rem;
        }
        .footer .logo-text { color: #fff; }
        .footer .logo-mark {
            background: rgba(255,255,255,0.10); color: #fff;
            border: 1px solid rgba(255,255,255,0.12);
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr 1fr;
            gap: 3rem;
        }
        @media (max-width: 880px) { .footer-grid { grid-template-columns: 1fr 1fr; gap: 2rem; } }
        @media (max-width: 540px) { .footer-grid { grid-template-columns: 1fr; } }
        .footer h5 {
            font-size: 12.5px; font-weight: 600;
            color: #fff; margin: 0 0 1rem;
            letter-spacing: -0.005em;
        }
        .footer ul li { margin-bottom: 0.55rem; }
        .footer ul a {
            font-size: 13.5px; color: rgba(255,255,255,0.78);
            transition: color 0.15s;
        }
        .footer ul a:hover { color: #fff; }
        .footer .blurb {
            font-size: 13.5px; color: rgba(255,255,255,0.78);
            line-height: 1.6; margin-top: 1rem; max-width: 22em;
        }
        .footer .socials { display: flex; gap: 0.5rem; margin-top: 1.25rem; }
        .footer .socials a {
            width: 32px; height: 32px; border-radius: 8px;
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.70);
            display: inline-flex; align-items: center; justify-content: center;
            transition: background 0.15s, color 0.15s;
        }
        .footer .socials a:hover { background: rgba(255,255,255,0.14); color: #fff; }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.12);
            margin-top: 3rem; padding-top: 1.5rem;
            font-size: 11.5px;
            color: rgba(255,255,255,0.72);
            display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
            font-family: var(--f-mono);
        }
        .footer .ftr-flag-icon { width: 36px; height: 36px; }

        /* Show on md only */
        @media (min-width: 880px) {
            [data-show-md] { display: inline-block !important; }
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
            <a href="{{ route('marketing.pricing') }}" class="nav-link" style="display: none;" data-show-md>{{ __('Pricing') }}</a>
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

<main>
{{ $slot }}
</main>

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
            <p class="blurb">{{ __('Citation-grounded contract drafting for corporate counsel across MENA jurisdictions.') }}</p>
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
                <li><a href="{{ route('marketing.jurisdiction', ['iso' => 'EG']) }}">{{ __('Egypt') }}</a></li>
                <li><a href="{{ route('marketing.jurisdiction', ['iso' => 'SA']) }}">{{ __('Saudi Arabia') }}</a></li>
                <li><a href="{{ route('marketing.jurisdiction', ['iso' => 'AE']) }}">{{ __('UAE') }}</a></li>
                <li><a href="{{ route('marketing.jurisdiction', ['iso' => 'KW']) }}">{{ __('Kuwait') }}</a></li>
                <li><a href="{{ route('marketing.jurisdiction', ['iso' => 'QA']) }}">{{ __('Qatar') }}</a></li>
                <li><a href="{{ route('marketing.jurisdiction', ['iso' => 'BH']) }}">{{ __('Bahrain') }}</a></li>
            </ul>
        </div>
        <div>
            <h5>{{ __('Resources') }}</h5>
            <ul>
                <li><a href="{{ route('marketing.pricing') }}">{{ __('Pricing') }}</a></li>
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
