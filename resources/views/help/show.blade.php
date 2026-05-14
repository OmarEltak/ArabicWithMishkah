@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $repo = app(\App\Services\Help\HelpArticleRepository::class);
    $article = $repo->find($slug, $locale);
    abort_unless($article !== null, 404);

    $pageTitle = $article['title'].' · '.($isAr ? 'مركز المساعدة' : 'Help').' · My-lawyer';
@endphp

<x-marketing.layout :page-title="$pageTitle">
    <section class="section section-tight">
        <div class="wrap-narrow">
            <p>
                <a href="{{ route('help.index') }}" class="btn-link">← {{ $isAr ? 'مركز المساعدة' : 'Help Center' }}</a>
            </p>
            <span class="eyebrow" style="margin-top: 1rem; display: inline-block;">{{ $article['section'] }}</span>
            <h1 class="display-serif" style="font-size: clamp(28px, 4vw, 44px); margin-top: 0.75rem;">
                {{ $article['title'] }}
            </h1>

            @if ($article['locale'] !== $locale)
                <p style="margin-top: 1rem; padding: 0.75rem 1rem; background: var(--surface); border: 1px solid var(--hairline); border-radius: 8px; font-size: 13px; color: var(--ink-mute);">
                    {{ $isAr ? 'هذه المقالة متاحة حالياً بالإنجليزية فقط.' : 'This article is currently only available in English.' }}
                </p>
            @endif

            <article class="help-article" style="margin-top: 2rem; font-size: 16px; line-height: 1.75; color: var(--ink-soft);">
                {!! $article['body_html'] !!}
            </article>

            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--hairline);">
                <p style="color: var(--ink-mute); font-size: 13px;">
                    {{ $isAr ? 'هل ساعدتك هذه المقالة؟ راسلنا إن كان شيء غير واضح.' : 'Was this helpful? Email us if anything is still unclear.' }}
                </p>
                <p style="margin-top: 0.5rem;">
                    <a href="mailto:{{ config('lawyer.support_email') }}" class="btn-link">{{ config('lawyer.support_email') }}</a>
                </p>
            </div>
        </div>
    </section>

    <style>
        .help-article h2 { font-family: var(--f-serif); font-size: 22px; font-weight: 600; color: var(--ink); margin: 2rem 0 0.75rem; letter-spacing: -0.015em; }
        :where(html[dir="rtl"]) .help-article h2 { font-family: var(--f-arabic); font-weight: 700; }
        .help-article h3 { font-family: var(--f-serif); font-size: 18px; font-weight: 600; color: var(--ink); margin: 1.5rem 0 0.5rem; }
        :where(html[dir="rtl"]) .help-article h3 { font-family: var(--f-arabic); font-weight: 700; }
        .help-article p { margin: 0 0 1rem; }
        .help-article ul, .help-article ol { margin: 0 0 1rem; padding-inline-start: 1.5rem; }
        .help-article li { margin: 0.35rem 0; }
        .help-article code { font-family: var(--f-mono); font-size: 13px; background: var(--surface); padding: 0.1em 0.35em; border-radius: 3px; color: var(--ink); }
        .help-article pre { background: var(--surface); padding: 1rem; border-radius: 8px; overflow-x: auto; font-family: var(--f-mono); font-size: 13px; margin: 1rem 0; border: 1px solid var(--hairline); }
        .help-article pre code { background: transparent; padding: 0; }
        .help-article blockquote { border-inline-start: 3px solid var(--hairline); padding-inline-start: 1rem; color: var(--ink-soft); font-style: italic; margin: 1rem 0; }
        .help-article a { color: var(--navy); border-bottom: 1px solid var(--hairline); transition: border-color 0.15s; }
        .help-article a:hover { border-bottom-color: var(--navy); }
        .help-article strong { color: var(--ink); font-weight: 600; }
        .help-article table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 14px; }
        .help-article th, .help-article td { padding: 0.65rem 0.85rem; border: 1px solid var(--hairline); text-align: start; }
        .help-article th { background: var(--surface); font-weight: 600; color: var(--ink); }
    </style>
</x-marketing.layout>
