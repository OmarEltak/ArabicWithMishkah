@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';

    $path = base_path('CHANGELOG.md');
    $raw = is_file($path) ? (string) file_get_contents($path) : '# Changelog'."\n";

    $converter = new \League\CommonMark\GithubFlavoredMarkdownConverter([
        'html_input' => 'escape',
        'allow_unsafe_links' => false,
    ]);
    $html = (string) $converter->convert($raw);

    $pageTitle = ($isAr ? 'سجل التغييرات' : 'Changelog').' · My-lawyer';
@endphp

<x-marketing.layout
    :page-title="$pageTitle"
    :page-description="$isAr ? 'سجل التغييرات الموجهة للمستخدم في منصة My-lawyer.' : 'User-facing changes shipped to My-lawyer.'"
>
    <section class="section section-tight">
        <div class="wrap-narrow">
            <span class="eyebrow">{{ $isAr ? 'التحديثات' : 'Updates' }}</span>
            <h1 class="display-serif" style="font-size: clamp(36px, 5vw, 56px); margin-top: 1rem;">
                {{ $isAr ? 'سجل التغييرات' : 'Changelog' }}
            </h1>
            <p class="lede" style="margin-top: 1.5rem;">
                {{ $isAr ? 'كل التحديثات الموجهة للمستخدم على المنصة، من الأحدث إلى الأقدم.' : 'Every user-facing change shipped to My-lawyer, newest first.' }}
            </p>

            <article class="changelog" style="margin-top: 3rem; font-size: 15.5px; line-height: 1.7; color: var(--ink-soft);">
                {!! $html !!}
            </article>
        </div>
    </section>

    <style>
        .changelog h1 { display: none; } /* hide the duplicate H1 from the markdown source */
        .changelog h2 { font-family: var(--f-mono); font-size: 13px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink); margin: 3rem 0 0.5rem; padding-top: 1.5rem; border-top: 1px solid var(--hairline); }
        .changelog h2:first-of-type { padding-top: 0; border-top: 0; margin-top: 1rem; }
        .changelog h3 { font-family: var(--f-serif); font-size: 17px; font-weight: 600; color: var(--ink); margin: 1.5rem 0 0.5rem; }
        :where(html[dir="rtl"]) .changelog h3 { font-family: var(--f-arabic); font-weight: 700; }
        .changelog p { margin: 0 0 0.85rem; }
        .changelog ul { margin: 0 0 1.25rem; padding-inline-start: 1.25rem; list-style-type: disc; }
        .changelog li { margin: 0.4rem 0; }
        .changelog code { font-family: var(--f-mono); font-size: 13px; background: var(--surface); padding: 0.1em 0.35em; border-radius: 3px; color: var(--ink); }
        .changelog strong { color: var(--ink); font-weight: 600; }
    </style>
</x-marketing.layout>
