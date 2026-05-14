@php
    $config = (array) config('eg_seo', []);
    $page = $config[$type] ?? null;
    abort_unless($page !== null, 404);

    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $canonical = url(request()->path());

    // FAQPage + LegalService structured data — these are what Google rich-results
    // pulls in for high-intent Arabic queries.
    $jsonLd = json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebPage',
                'name' => $page['title_ar'],
                'description' => $page['meta_description_ar'],
                'url' => $canonical,
                'inLanguage' => 'ar',
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => array_map(fn ($qa) => [
                    '@type' => 'Question',
                    'name' => $qa['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa['a']],
                ], $page['faq']),
            ],
            [
                '@type' => 'LegalService',
                'name' => 'My-lawyer',
                'areaServed' => ['@type' => 'Country', 'name' => 'Egypt'],
                'serviceType' => $page['title_en'],
                'priceRange' => 'EGP 999/mo',
                'url' => url('/eg'),
            ],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp

<x-marketing.layout
    :page-title="$page['title_ar']"
    :page-description="$page['meta_description_ar']"
    :canonical="$canonical"
    :json-ld="$jsonLd"
>
    <style>
        .eg-c-hero { padding: clamp(3rem, 6vw, 5rem) 0 clamp(2rem, 4vw, 3rem); }
        .eg-c-hero h1 {
            font-family: var(--f-arabic);
            font-size: clamp(30px, 4.5vw, 48px);
            font-weight: 700;
            line-height: 1.18;
            color: var(--ink);
            margin: 1rem 0 1.25rem;
        }
        .eg-c-hero .intro {
            font-family: var(--f-arabic);
            font-size: 17px; line-height: 1.95;
            color: var(--ink-soft);
            max-width: 38rem;
        }
        .eg-c-kw {
            display: flex; flex-wrap: wrap; gap: 0.5rem;
            margin-top: 1.5rem;
        }
        .eg-c-kw span {
            font-family: var(--f-mono);
            font-size: 11.5px;
            color: var(--ink-mute);
            background: var(--surface);
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
        }

        .eg-c-section {
            padding: clamp(2.5rem, 5vw, 4rem) 0;
            border-top: 1px solid var(--hairline);
        }
        .eg-c-section h2 {
            font-family: var(--f-serif);
            font-size: clamp(24px, 3.5vw, 32px);
            font-weight: 500;
            color: var(--ink);
            letter-spacing: -0.012em;
            margin: 0 0 1.5rem;
        }
        :where(html[dir="rtl"]) .eg-c-section h2 { font-family: var(--f-arabic); font-weight: 700; }

        .eg-statutes { display: grid; gap: 0.85rem; }
        .eg-statute {
            display: flex; gap: 1rem;
            padding: 1rem 1.15rem;
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 10px;
        }
        .eg-statute .num {
            font-family: var(--f-mono);
            font-size: 11px; color: var(--gold-deep);
            letter-spacing: 0.1em;
            flex-shrink: 0; padding-top: 2px;
        }
        .eg-statute .body { flex: 1; }
        .eg-statute .ref { font-family: var(--f-arabic); font-weight: 600; color: var(--ink); font-size: 15px; }
        .eg-statute .why { font-family: var(--f-arabic); color: var(--ink-soft); font-size: 13.5px; margin-top: 0.25rem; line-height: 1.6; }

        .eg-clauses { display: grid; gap: 0.65rem; grid-template-columns: 1fr; max-width: 36rem; }
        @media (min-width: 600px) { .eg-clauses { grid-template-columns: 1fr 1fr; } }
        .eg-clauses li {
            font-family: var(--f-arabic);
            font-size: 14.5px;
            color: var(--ink-soft);
            background: var(--surface);
            padding: 0.65rem 0.95rem;
            border-radius: 8px;
            display: flex; gap: 0.5rem; align-items: center;
        }
        .eg-clauses li::before { content: "✓"; color: var(--green); font-weight: 700; font-family: var(--f-mono); }

        .eg-faq details {
            border: 1px solid var(--hairline);
            border-radius: 10px;
            background: var(--canvas);
            margin-bottom: 0.75rem;
            overflow: hidden;
        }
        .eg-faq summary {
            padding: 1rem 1.15rem;
            font-family: var(--f-arabic);
            font-weight: 600;
            font-size: 15.5px;
            color: var(--ink);
            cursor: pointer;
            list-style: none;
            display: flex; justify-content: space-between; align-items: center; gap: 1rem;
        }
        .eg-faq summary::-webkit-details-marker { display: none; }
        .eg-faq summary::after { content: "+"; color: var(--ink-mute); font-family: var(--f-mono); font-size: 18px; flex-shrink: 0; }
        .eg-faq details[open] summary::after { content: "−"; }
        .eg-faq details[open] summary { border-bottom: 1px solid var(--hairline); }
        .eg-faq .answer {
            padding: 1rem 1.15rem 1.25rem;
            font-family: var(--f-arabic);
            font-size: 14.5px; line-height: 1.85;
            color: var(--ink-soft);
        }

        .eg-c-cta { text-align: center; padding: clamp(3rem, 6vw, 4.5rem) 0; }
        .eg-c-cta h2 { max-width: 28ch; margin: 0 auto 1.25rem; }
        .eg-c-cta .lede { margin: 0 auto 2rem; }
    </style>

    <section class="eg-c-hero" dir="rtl">
        <div class="wrap-narrow">
            <p>
                <a href="{{ route('marketing.eg') }}" class="btn-link">← {{ $isAr ? 'الصفحة الرئيسية لمصر' : 'Egypt landing' }}</a>
            </p>
            <span class="eyebrow">{{ $isAr ? 'صياغة العقود المصرية' : 'Egyptian contract drafting' }}</span>
            <h1>{{ $page['h1_ar'] }}</h1>
            <p class="intro">{{ $page['intro_ar'] }}</p>
            <div class="eg-c-kw">
                @foreach ($page['keywords'] as $kw)
                    <span>{{ $kw }}</span>
                @endforeach
            </div>
            <div style="margin-top: 2rem;">
                <a href="{{ route('register') }}" class="btn-primary">{{ $isAr ? 'ابدأ المسودة الآن — تجربة ١٤ يوم مجاناً' : 'Start drafting — 14-day free trial' }}</a>
            </div>
        </div>
    </section>

    <section class="eg-c-section" dir="rtl">
        <div class="wrap-narrow">
            <h2>القوانين والمواد المرجعية</h2>
            <p class="lede" style="margin-bottom: 2rem;">كل بند في المسودة بيتم التحقق من استشهاده بهذه النصوص قبل ما توصلك.</p>
            <div class="eg-statutes">
                @foreach ($page['statutes'] as $i => $s)
                    <div class="eg-statute">
                        <span class="num">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="body">
                            <div class="ref">{{ $s['ref'] }}</div>
                            <div class="why">{{ $s['why'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="eg-c-section" dir="rtl">
        <div class="wrap-narrow">
            <h2>البنود اللي My-lawyer بتولّدها</h2>
            <ul class="eg-clauses" style="list-style: none; padding: 0; margin: 0;">
                @foreach ($page['clauses'] as $c)
                    <li>{{ $c }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="eg-c-section eg-faq" dir="rtl">
        <div class="wrap-narrow">
            <h2>أسئلة شائعة</h2>
            @foreach ($page['faq'] as $qa)
                <details>
                    <summary>{{ $qa['q'] }}</summary>
                    <div class="answer">{{ $qa['a'] }}</div>
                </details>
            @endforeach
        </div>
    </section>

    <section class="eg-c-cta" dir="rtl">
        <div class="wrap-narrow">
            <h2 class="display-serif" style="font-size: clamp(26px, 4vw, 36px);">جاهز تصيغ {{ $page['h1_ar'] }}؟</h2>
            <p class="lede">١٤ يوم تجربة مجانية، بدون بطاقة. مسودتك الأولى بتاخد أقل من ١٠ دقائق.</p>
            <div>
                <a href="{{ route('register') }}" class="btn-primary">ابدأ الآن</a>
                <a href="{{ route('marketing.eg') }}" class="btn-outline" style="margin-inline-start: 0.5rem;">شاهد كل المميزات</a>
            </div>
        </div>
    </section>
</x-marketing.layout>
