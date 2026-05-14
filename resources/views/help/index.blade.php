@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $repo = app(\App\Services\Help\HelpArticleRepository::class);

    $q = trim((string) request()->query('q', ''));
    $results = $q !== '' ? $repo->search($locale, $q) : null;
    $articles = $repo->index($locale);

    // Group by section.
    $sections = [];
    foreach ($articles as $a) {
        $sections[$a['section']][] = $a;
    }

    $pageTitle = ($isAr ? 'مركز المساعدة' : 'Help Center').' · My-lawyer';
    $pageDescription = $isAr
        ? 'كيف تستخدم My-lawyer لصياغة العقود ثنائية اللغة الموثقة بالاستشهادات تحت قوانين دول الشرق الأوسط وشمال إفريقيا.'
        : 'How-to articles for drafting citation-grounded bilingual contracts under MENA jurisdictions with My-lawyer.';
@endphp

<x-marketing.layout
    :page-title="$pageTitle"
    :page-description="$pageDescription"
>
    <section class="section">
        <div class="wrap-narrow">
            <span class="eyebrow">{{ $isAr ? 'مركز المساعدة' : 'Help Center' }}</span>
            <h1 class="display-serif" style="font-size: clamp(36px, 5vw, 56px); margin-top: 1rem;">
                {{ $isAr ? 'كيف نستطيع المساعدة؟' : 'How can we help?' }}
            </h1>
            <p class="lede" style="margin-top: 1.5rem;">
                {{ $isAr ? 'إرشادات قصيرة لصياغة العقود، إدارة الموضوعات، التصدير، والاستخدام اليومي للمنصة.' : 'Short guides on drafting, managing matters, exporting, and using the platform day-to-day.' }}
            </p>

            <form method="GET" action="{{ route('help.index') }}" style="margin-top: 2rem; max-width: 32rem;">
                <input type="search"
                    name="q"
                    value="{{ $q }}"
                    placeholder="{{ $isAr ? 'ابحث في مركز المساعدة…' : 'Search the help center…' }}"
                    style="width: 100%; padding: 0.85rem 1.1rem; font-size: 15px; border: 1px solid var(--hairline); border-radius: 10px; background: var(--canvas); color: var(--ink); font-family: inherit;"
                    autofocus />
            </form>
        </div>
    </section>

    @if ($results !== null)
        <section class="section section-tight">
            <div class="wrap-narrow">
                <span class="eyebrow">
                    {{ $isAr ? 'النتائج' : 'Results' }} · {{ count($results) }}
                </span>
                @if (count($results) === 0)
                    <p style="margin-top: 1.25rem; color: var(--ink-mute);">
                        {{ $isAr ? 'لم نجد شيئاً يطابق' : 'Nothing matched' }} <strong>"{{ e($q) }}"</strong>.
                    </p>
                @else
                    <ul style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                        @foreach ($results as $r)
                            <li>
                                <a href="{{ route('help.show', ['slug' => $r['slug']]) }}" style="display: block; padding: 1.1rem 1.25rem; border: 1px solid var(--hairline); border-radius: 10px; transition: border-color 0.15s, background 0.15s;">
                                    <span style="display: block; font-family: var(--f-mono); font-size: 11px; color: var(--ink-mute); letter-spacing: 0.1em; text-transform: uppercase;">{{ $r['section'] }}</span>
                                    <span style="display: block; margin-top: 0.35rem; font-size: 17px; font-weight: 600; color: var(--ink);">{{ $r['title'] }}</span>
                                    <span style="display: block; margin-top: 0.35rem; font-size: 13.5px; color: var(--ink-soft); line-height: 1.6;">{{ $r['snippet'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <p style="margin-top: 2rem;">
                    <a href="{{ route('help.index') }}" class="btn-link">← {{ $isAr ? 'العودة إلى الفهرس' : 'Back to all articles' }}</a>
                </p>
            </div>
        </section>
    @else
        <section class="section section-tight">
            <div class="wrap-narrow">
                @foreach ($sections as $sectionName => $items)
                    <div style="margin-top: 2.5rem;">
                        <span class="eyebrow">{{ $sectionName }}</span>
                        <ul style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                            @foreach ($items as $a)
                                <li>
                                    <a href="{{ route('help.show', ['slug' => $a['slug']]) }}" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 0.85rem 1.1rem; border: 1px solid var(--hairline); border-radius: 8px; transition: border-color 0.15s, background 0.15s;">
                                        <span style="font-size: 15px; font-weight: 500; color: var(--ink);">{{ $a['title'] }}</span>
                                        <span style="color: var(--ink-mute);">→</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach

                <div style="margin-top: 3rem; padding: 1.75rem; border: 1px dashed var(--hairline); border-radius: 12px; text-align: center;">
                    <p style="color: var(--ink-soft); font-size: 14px;">
                        {{ $isAr ? 'لم تجد ما تبحث عنه؟ تواصل معنا.' : 'Didn\'t find what you were looking for? Reach out.' }}
                    </p>
                    <p style="margin-top: 0.5rem;">
                        <a href="mailto:{{ config('lawyer.support_email') }}" class="btn-outline" style="display: inline-flex;">
                            {{ $isAr ? 'مراسلة الدعم' : 'Email support' }}
                        </a>
                    </p>
                </div>
            </div>
        </section>
    @endif
</x-marketing.layout>
