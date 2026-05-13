@props([
    'code' => '500',
    'title' => null,
    'heading' => null,
    'message' => null,
    'primaryHref' => null,
    'primaryLabel' => null,
    'secondaryHref' => null,
    'secondaryLabel' => null,
])
@php
    // Error pages must NOT depend on the database, the auth session, the
    // route table, or Vite/asset compilation — they have to render even
    // when those subsystems are the reason we're showing an error.
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $dir = $isAr ? 'rtl' : 'ltr';
    $themeClass = request()->cookie('theme') === 'dark' ? 'dark' : '';

    $title = $title ?? ($heading ?? ($code.' · My-lawyer'));
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}" class="{{ $themeClass }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600&family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --canvas: #FFFFFF;
            --surface: #F4F6F9;
            --ink: #0B1626;
            --ink-soft: #3F4A5A;
            --ink-mute: #5C6677;
            --hairline: #DCE2EA;
            --navy: #0F2942;
            --navy-deep: #0A1E33;
            --navy-soft: #E1E9F6;
            --gold-deep: #B8932F;
            --f-serif: 'Source Serif 4', Georgia, serif;
            --f-sans: 'Inter', system-ui, -apple-system, sans-serif;
            --f-mono: 'JetBrains Mono', ui-monospace, monospace;
            --f-arabic: 'Tajawal', 'Inter', system-ui, sans-serif;
        }
        html.dark {
            --canvas: #0A0F18;
            --surface: #131B2C;
            --ink: #F6F8FB;
            --ink-soft: #C5CDD9;
            --ink-mute: #98A3B5;
            --hairline: #243049;
            --navy: #0F2942;
            --navy-deep: #050D17;
            --navy-soft: rgba(140, 170, 230, 0.16);
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: var(--canvas); color: var(--ink); }
        body {
            font-family: var(--f-sans);
            -webkit-font-smoothing: antialiased;
            line-height: 1.5;
            min-height: 100vh;
            display: flex; flex-direction: column;
        }
        :where(html[dir="rtl"]) body { font-family: var(--f-arabic); }
        a { color: inherit; text-decoration: none; }

        .nav-shell {
            border-bottom: 1px solid var(--hairline);
            background: var(--canvas);
        }
        .nav-inner {
            max-width: 1200px; margin: 0 auto;
            padding: 0 1.5rem;
            height: 72px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .brand { display: inline-flex; align-items: center; gap: 0.65rem; color: var(--ink); }
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

        main { flex: 1; display: flex; align-items: center; }
        .wrap-narrow {
            max-width: 720px; margin: 0 auto;
            padding: clamp(3rem, 8vw, 6rem) 1.5rem;
        }

        .eyebrow {
            font-family: var(--f-mono);
            font-size: 11px; font-weight: 500;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--ink-mute);
            display: inline-flex; align-items: center; gap: 0.5rem;
        }
        .eyebrow .pip {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--gold-deep);
        }
        .err-code {
            font-family: var(--f-mono);
            font-size: 13px;
            color: var(--ink-mute);
            letter-spacing: 0.04em;
        }

        h1 {
            font-family: var(--f-serif);
            font-size: clamp(36px, 5.5vw, 60px);
            font-weight: 500;
            letter-spacing: -0.022em;
            line-height: 1.06;
            color: var(--ink);
            margin: 1rem 0 1.25rem;
        }
        :where(html[dir="rtl"]) h1 {
            font-family: var(--f-arabic);
            font-weight: 700;
            line-height: 1.18;
        }
        .lede {
            color: var(--ink-soft);
            font-size: 17px; line-height: 1.65;
            margin: 0;
        }
        :where(html[dir="rtl"]) .lede { font-size: 17.5px; line-height: 1.85; }

        .actions {
            display: flex; flex-wrap: wrap; gap: 0.75rem;
            margin-top: 2rem;
        }
        .btn-primary {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.85rem 1.4rem;
            background: var(--navy); color: #fff;
            border: 1px solid var(--navy);
            border-radius: 10px;
            font-size: 14px; font-weight: 600;
            transition: background 0.15s, transform 0.15s;
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
        }
        .btn-outline:hover { border-color: var(--ink); background: var(--surface); }

        .meta-line {
            margin-top: 2.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--hairline);
            font-size: 13px;
            color: var(--ink-mute);
            display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem;
            align-items: center;
        }
        .meta-line a { color: var(--ink-soft); border-bottom: 1px solid transparent; transition: border-color 0.15s; }
        .meta-line a:hover { border-bottom-color: var(--ink-soft); }

        footer {
            border-top: 1px solid var(--hairline);
            padding: 1.5rem;
            font-size: 12px;
            color: var(--ink-mute);
            text-align: center;
        }

        @media (max-width: 600px) {
            .nav-inner { height: 60px; padding: 0 1rem; }
            .logo-text { font-size: 18px; }
            .wrap-narrow { padding-top: 2.5rem; padding-bottom: 2.5rem; }
        }
    </style>
</head>
<body>

<header class="nav-shell">
    <div class="nav-inner">
        <a href="/" class="brand" aria-label="My-lawyer">
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
        <span class="err-code">{{ $isAr ? 'خطأ' : 'Error' }} · {{ $code }}</span>
    </div>
</header>

<main>
    <div class="wrap-narrow">
        <span class="eyebrow">
            <span class="pip" aria-hidden="true"></span>
            {{ $isAr ? 'رمز الحالة' : 'Status' }} {{ $code }}
        </span>

        <h1>{{ $heading }}</h1>

        <p class="lede">{{ $message }}</p>

        <div class="actions">
            @if ($primaryHref && $primaryLabel)
                <a href="{{ $primaryHref }}" class="btn-primary">{{ $primaryLabel }}</a>
            @endif
            @if ($secondaryHref && $secondaryLabel)
                <a href="{{ $secondaryHref }}" class="btn-outline">{{ $secondaryLabel }}</a>
            @endif
        </div>

        {{ $slot ?? '' }}

        <div class="meta-line">
            <span>{{ $isAr ? 'تحتاج إلى مساعدة؟' : 'Need help?' }}</span>
            <a href="/faq">{{ $isAr ? 'الأسئلة الشائعة' : 'FAQ' }}</a>
            <a href="mailto:{{ config('lawyer.support_email', 'support@my-lawyer.app') }}">{{ $isAr ? 'تواصل مع الدعم' : 'Contact support' }}</a>
        </div>
    </div>
</main>

<footer>
    © {{ date('Y') }} My-lawyer · {{ $isAr ? 'صياغة قانونية موثقة بالاستشهادات' : 'Citation-grounded legal drafting' }}
</footer>

</body>
</html>
