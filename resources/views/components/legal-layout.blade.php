@props([
    'pageTitle',
    'pageDescription',
    'heading',
    'intro',
    'effectiveDate',
    'sections' => [],
    'canonical' => null,
])
<x-marketing.layout
    :page-title="$pageTitle"
    :page-description="$pageDescription"
    :canonical="$canonical"
>

<style>
    .legal-wrap {
        max-width: 1180px;
        margin: 0 auto;
        padding: clamp(2rem, 5vw, 3.5rem) 1.5rem clamp(3rem, 6vw, 5rem);
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    @media (min-width: 1000px) {
        .legal-wrap {
            grid-template-columns: minmax(0, 1fr) 240px;
            padding: clamp(2rem, 5vw, 3.5rem) 2.5rem clamp(3rem, 6vw, 5rem);
        }
        :where(html[dir="rtl"]) .legal-wrap { grid-template-columns: 240px minmax(0, 1fr); }
    }
    .legal-toc {
        position: sticky;
        top: 88px;
        align-self: start;
        font-family: var(--f-mono);
        font-size: 11.5px;
        line-height: 1.7;
        padding: 1rem 1.25rem;
        background: var(--surface);
        border: 1px solid var(--hairline);
        border-radius: 12px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
    }
    .legal-toc h4 {
        font-family: var(--f-sans);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--ink-mute);
        margin: 0 0 0.85rem;
    }
    .legal-toc ol {
        list-style: none;
        margin: 0;
        padding: 0;
        counter-reset: toc;
    }
    .legal-toc ol li {
        counter-increment: toc;
        margin-bottom: 0.4rem;
    }
    .legal-toc ol li a {
        color: var(--ink-soft);
        transition: color 0.15s;
        display: block;
        padding: 2px 0;
    }
    .legal-toc ol li a:hover { color: var(--navy); }
    .legal-toc ol li a::before {
        content: counter(toc, decimal-leading-zero);
        font-weight: 600;
        color: var(--navy);
        margin-inline-end: 0.6rem;
    }

    article.legal-article {
        max-width: 720px;
    }
    article.legal-article header {
        margin-bottom: 2.5rem;
        padding-bottom: 1.75rem;
        border-bottom: 1px solid var(--hairline);
    }
    article.legal-article h1 {
        font-family: var(--f-serif);
        font-size: clamp(32px, 4.6vw, 48px);
        font-weight: 500;
        letter-spacing: -0.022em;
        line-height: 1.1;
        margin: 1rem 0 1.25rem;
        color: var(--ink);
    }
    :where(html[dir="rtl"]) article.legal-article h1 { font-family: var(--f-arabic); font-weight: 700; line-height: 1.25; }
    article.legal-article header .meta {
        font-family: var(--f-mono);
        font-size: 11.5px;
        color: var(--ink-mute);
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        margin-top: 1rem;
    }
    article.legal-article header .intro {
        font-size: 16.5px;
        line-height: 1.7;
        color: var(--ink-soft);
        max-width: 36em;
        margin-top: 0.75rem;
    }
    :where(html[dir="rtl"]) article.legal-article header .intro { font-size: 17px; line-height: 1.85; }

    article.legal-article section {
        margin-bottom: 2.5rem;
        scroll-margin-top: 88px;
    }
    article.legal-article h2 {
        font-family: var(--f-serif);
        font-size: clamp(20px, 2.4vw, 26px);
        font-weight: 600;
        letter-spacing: -0.015em;
        line-height: 1.25;
        margin: 0 0 1rem;
        color: var(--ink);
        display: flex;
        align-items: baseline;
        gap: 0.6rem;
    }
    :where(html[dir="rtl"]) article.legal-article h2 { font-family: var(--f-arabic); font-weight: 700; }
    article.legal-article h2 .num {
        font-family: var(--f-mono);
        font-size: 13px;
        font-weight: 600;
        color: var(--navy);
        flex-shrink: 0;
    }
    article.legal-article h3 {
        font-family: var(--f-sans);
        font-size: 16px;
        font-weight: 600;
        letter-spacing: -0.01em;
        margin: 1.5rem 0 0.65rem;
        color: var(--ink);
    }
    :where(html[dir="rtl"]) article.legal-article h3 { font-family: var(--f-arabic); font-weight: 700; }
    article.legal-article p,
    article.legal-article li {
        font-size: 15px;
        line-height: 1.75;
        color: var(--ink);
    }
    :where(html[dir="rtl"]) article.legal-article p,
    :where(html[dir="rtl"]) article.legal-article li {
        font-size: 16px;
        line-height: 1.95;
    }
    article.legal-article p { margin: 0 0 1rem; }
    article.legal-article ul,
    article.legal-article ol {
        margin: 0 0 1rem;
        padding-inline-start: 1.5rem;
    }
    article.legal-article ul li { list-style: disc; margin-bottom: 0.4rem; }
    article.legal-article ol li { list-style: decimal; margin-bottom: 0.4rem; }
    article.legal-article strong { font-weight: 600; color: var(--ink); }
    article.legal-article a { color: var(--navy); text-decoration: underline; text-underline-offset: 3px; }
    article.legal-article a:hover { text-decoration-thickness: 2px; }
    article.legal-article .callout {
        margin: 1.5rem 0;
        padding: 1rem 1.25rem;
        background: var(--surface);
        border: 1px solid var(--hairline);
        border-inline-start: 3px solid var(--navy);
        border-radius: 6px;
        font-size: 14px;
        line-height: 1.65;
        color: var(--ink-soft);
    }
    article.legal-article .callout strong { color: var(--ink); }
    article.legal-article .definition {
        margin: 1rem 0;
        padding: 0.85rem 1.1rem;
        background: var(--gold);
        border-radius: 8px;
        border: 1px solid #EFE2BC;
    }
    article.legal-article .definition .term {
        font-family: var(--f-serif);
        font-weight: 600;
        font-style: italic;
        color: var(--ink);
    }
    :where(html[dir="rtl"]) article.legal-article .definition .term {
        font-family: var(--f-arabic);
        font-style: normal;
    }
    article.legal-article table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0 1.5rem;
        background: var(--canvas);
        border: 1px solid var(--hairline);
        border-radius: 8px;
        overflow: hidden;
        font-size: 14px;
    }
    article.legal-article th {
        background: var(--surface);
        padding: 0.65rem 0.85rem;
        text-align: start;
        font-family: var(--f-mono);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--ink-mute);
        border-bottom: 1px solid var(--hairline);
    }
    article.legal-article td {
        padding: 0.65rem 0.85rem;
        border-bottom: 1px solid var(--hairline-soft);
        vertical-align: top;
    }
    article.legal-article tr:last-child td { border-bottom: 0; }

    .draft-banner {
        margin: 0 0 2rem;
        padding: 0.85rem 1.25rem;
        background: #FFF7E0;
        border: 1px solid #F4D67F;
        border-radius: 8px;
        font-size: 13px;
        line-height: 1.55;
        color: #5C4708;
    }
    html.dark .draft-banner {
        background: rgba(184, 147, 47, 0.12);
        border-color: rgba(184, 147, 47, 0.32);
        color: #E5C770;
    }
    .draft-banner strong { font-weight: 700; }

    @media print {
        .nav-shell, .footer, .legal-toc, .draft-banner { display: none !important; }
        article.legal-article { max-width: none; }
    }
</style>

<div class="legal-wrap">

    <article class="legal-article">
        <header>
            <span class="badge-tag">
                <span class="pip"></span>{{ __('Legal') }}
            </span>
            <h1>{{ $heading }}</h1>
            <p class="intro">{{ $intro }}</p>
            <div class="meta">
                <span>{{ __('Effective') }}: {{ $effectiveDate }}</span>
                <span>{{ __('Governing law') }}: {{ __('Arab Republic of Egypt') }}</span>
                <span>{{ __('Forum') }}: {{ __('Cairo Economic Courts') }}</span>
            </div>
        </header>

        <div class="draft-banner">
            <strong>{{ __('Document status') }}:</strong>
            {{ __('This is a working draft prepared as a starting point. Before relying on it commercially, have it reviewed by a licensed Egyptian lawyer of your choice.') }}
        </div>

        {{ $slot }}
    </article>

    <aside class="legal-toc" aria-label="{{ __('Sections') }}">
        <h4>{{ __('On this page') }}</h4>
        <ol>
            @foreach ($sections as $s)
                <li><a href="#{{ $s['id'] }}">{{ $s['title'] }}</a></li>
            @endforeach
        </ol>
    </aside>

</div>

</x-marketing.layout>
