@php
    $isAr = app()->getLocale() === 'ar';
    $pageTitle = $isAr
        ? 'My-lawyer · صياغة العقود بالذكاء الاصطناعي تحت القانون المصري'
        : 'My-lawyer · AI contract drafting for Egyptian corporate lawyers';
    $pageDescription = $isAr
        ? 'أداة صياغة عقود ثنائية اللغة لمحامي الشركات في القاهرة. كل بند موثق بالقانون المدني والشركات وأحكام محكمة النقض. مصرية الصنع. الأسعار بالجنيه.'
        : 'Bilingual contract drafting for Cairo corporate lawyers. Every clause verified against the Egyptian Civil Code, Companies Law, and Cassation jurisprudence. Built in Cairo. Priced in EGP.';
@endphp

<x-marketing.layout
    :page-title="$pageTitle"
    :page-description="$pageDescription"
>
    <style>
        .eg-hero {
            padding: clamp(4rem, 9vw, 7rem) 0;
            background:
                radial-gradient(circle at 15% 10%, rgba(184, 147, 47, 0.06), transparent 50%),
                radial-gradient(circle at 85% 90%, rgba(15, 41, 66, 0.04), transparent 50%),
                var(--canvas);
        }
        .eg-hero .pill-eg {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.4rem 0.85rem;
            background: rgba(184, 147, 47, 0.12);
            color: var(--gold-deep);
            border: 1px solid rgba(184, 147, 47, 0.3);
            border-radius: 9999px;
            font-family: var(--f-mono);
            font-size: 11px; font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .eg-hero h1 {
            font-family: var(--f-serif);
            font-size: clamp(40px, 6vw, 64px);
            font-weight: 500;
            letter-spacing: -0.024em;
            line-height: 1.04;
            margin: 1.25rem 0 1.5rem;
            color: var(--ink);
            max-width: 22ch;
        }
        :where(html[dir="rtl"]) .eg-hero h1 {
            font-family: var(--f-arabic);
            font-weight: 700;
            line-height: 1.16;
            max-width: 22ch;
        }
        .eg-anchors {
            display: flex; flex-wrap: wrap; gap: 0.75rem;
            margin-top: 2rem;
        }
        .eg-anchors .anchor {
            display: inline-flex; align-items: center; gap: 0.45rem;
            padding: 0.45rem 0.85rem;
            background: var(--surface);
            border: 1px solid var(--hairline);
            border-radius: 8px;
            font-size: 13px; font-weight: 500;
            color: var(--ink-soft);
        }
        .eg-anchors .anchor strong {
            color: var(--ink); font-weight: 700;
        }

        .eg-three {
            padding: clamp(3rem, 6vw, 5rem) 0;
            background: var(--surface);
            border-top: 1px solid var(--hairline);
            border-bottom: 1px solid var(--hairline);
        }
        .eg-three-grid {
            display: grid; gap: 1.5rem;
            grid-template-columns: 1fr;
            max-width: 1100px; margin: 0 auto;
        }
        @media (min-width: 768px) { .eg-three-grid { grid-template-columns: repeat(3, 1fr); } }
        .eg-three-card {
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 14px;
            padding: 1.75rem;
        }
        .eg-three-card .num {
            font-family: var(--f-mono);
            font-size: 11px;
            color: var(--gold-deep);
            letter-spacing: 0.16em;
            font-weight: 600;
        }
        .eg-three-card h3 {
            font-family: var(--f-serif);
            font-size: 21px; font-weight: 600;
            color: var(--ink);
            letter-spacing: -0.01em;
            margin: 0.75rem 0 0.75rem;
        }
        :where(html[dir="rtl"]) .eg-three-card h3 { font-family: var(--f-arabic); font-weight: 700; }
        .eg-three-card p {
            font-size: 14.5px; line-height: 1.65;
            color: var(--ink-soft); margin: 0;
        }

        .eg-pricing {
            padding: clamp(3rem, 6vw, 5rem) 0;
        }
        .eg-price-card {
            background: var(--canvas);
            border: 1px solid var(--hairline);
            border-radius: 14px;
            padding: 2rem;
            max-width: 480px; margin: 0 auto;
            box-shadow: 0 12px 32px -16px rgba(15, 41, 66, 0.12);
        }
        .eg-price-card .label {
            font-family: var(--f-mono);
            font-size: 11px;
            color: var(--gold-deep);
            letter-spacing: 0.16em;
            font-weight: 600;
            text-transform: uppercase;
        }
        .eg-price-card .price {
            font-family: var(--f-serif);
            font-size: 56px; font-weight: 500;
            color: var(--ink);
            line-height: 1; letter-spacing: -0.02em;
            margin: 0.75rem 0 0.25rem;
        }
        :where(html[dir="rtl"]) .eg-price-card .price { font-family: var(--f-arabic); font-weight: 700; }
        .eg-price-card .price .unit { font-size: 18px; color: var(--ink-mute); letter-spacing: 0; }
        .eg-price-card .sub { font-size: 14px; color: var(--ink-soft); margin: 0 0 1.25rem; }
        .eg-price-card ul {
            margin: 0 0 1.75rem; padding: 0; list-style: none;
            display: flex; flex-direction: column; gap: 0.65rem;
        }
        .eg-price-card li {
            display: flex; gap: 0.65rem; align-items: flex-start;
            font-size: 14.5px; line-height: 1.55;
            color: var(--ink-soft);
        }
        .eg-price-card li::before {
            content: "✓"; color: var(--green); font-weight: 700; flex-shrink: 0;
            font-family: var(--f-mono);
        }

        .eg-trust {
            padding: clamp(3rem, 5vw, 4.5rem) 0;
            background: var(--surface);
            border-top: 1px solid var(--hairline);
        }
        .trust-row {
            display: grid; gap: 1.25rem;
            grid-template-columns: 1fr; max-width: 900px; margin: 1.5rem auto 0;
        }
        @media (min-width: 768px) { .trust-row { grid-template-columns: repeat(4, 1fr); } }
        .trust-item {
            text-align: center; padding: 1rem 0.75rem;
            border-inline-start: 1px solid var(--hairline);
        }
        .trust-item:first-child { border-inline-start: 0; }
        @media (max-width: 767px) { .trust-item { border-inline-start: 0; border-top: 1px solid var(--hairline); padding-top: 1.25rem; } .trust-item:first-child { border-top: 0; padding-top: 0.25rem; } }
        .trust-item .big {
            font-family: var(--f-serif);
            font-size: 28px; font-weight: 600;
            color: var(--ink);
            letter-spacing: -0.015em;
        }
        :where(html[dir="rtl"]) .trust-item .big { font-family: var(--f-arabic); font-weight: 700; }
        .trust-item .lbl {
            font-size: 12.5px; color: var(--ink-mute);
            margin-top: 0.35rem; line-height: 1.4;
        }

        .eg-cta {
            padding: clamp(3.5rem, 6vw, 5.5rem) 0;
            text-align: center;
        }
        .eg-cta h2 {
            font-family: var(--f-serif);
            font-size: clamp(28px, 4vw, 40px);
            font-weight: 500;
            letter-spacing: -0.018em;
            max-width: 22ch; margin: 0 auto 1.25rem;
            color: var(--ink);
        }
        :where(html[dir="rtl"]) .eg-cta h2 { font-family: var(--f-arabic); font-weight: 700; }
    </style>

    <section class="eg-hero">
        <div class="wrap-narrow">
            <span class="pill-eg">
                <span aria-hidden="true">🇪🇬</span>
                {{ $isAr ? 'مصرية الصنع — للمحامين المصريين' : 'Built in Cairo — for Egyptian corporate lawyers' }}
            </span>

            <h1>
                {{ $isAr
                    ? 'صياغة العقود تحت القانون المصري، بنصف الوقت.'
                    : 'Draft Egyptian contracts in half the time.' }}
            </h1>

            <p class="lede">
                {{ $isAr
                    ? 'كل بند مدعوم بالقانون المدني، قانون الشركات، قانون العمل، وأحكام محكمة النقض. تصدير ثنائي اللغة بضغطة واحدة. السعر بالجنيه.'
                    : 'Every clause grounded in the Egyptian Civil Code, Companies Law, Labour Code, and Cassation jurisprudence. Bilingual export in one click. Priced in EGP.' }}
            </p>

            <div class="eg-anchors">
                <span class="anchor"><strong>{{ $isAr ? 'الباقة' : 'Starter' }}</strong> {{ $isAr ? 'بـ' : '·' }} {{ $isAr ? '٩٩٩ جنيه/شهر' : 'EGP 999/mo' }}</span>
                <span class="anchor"><strong>{{ $isAr ? '١٤ يوم' : '14-day' }}</strong> {{ $isAr ? 'تجربة مجانية' : 'free trial' }}</span>
                <span class="anchor"><strong>{{ $isAr ? 'بدون بطاقة' : 'No card' }}</strong> {{ $isAr ? 'مطلوبة' : 'required' }}</span>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2rem;">
                <a href="{{ route('register') }}" class="btn-primary">{{ $isAr ? 'ابدأ التجربة المجانية' : 'Start free trial' }}</a>
                <a href="#how" class="btn-outline">{{ $isAr ? 'كيف تعمل' : 'How it works' }}</a>
            </div>

            <p style="margin-top: 2rem; font-size: 12.5px; color: var(--ink-mute);">
                {{ $isAr
                    ? 'يستخدمها محامون شركات في القاهرة، المعادي، والتجمع الخامس. مكتبة قانونية تشمل ١١ دولة عربية، أعمقها مصر.'
                    : 'Used by corporate lawyers in Cairo, Maadi, and 5th Settlement. Corpus covers 11 MENA jurisdictions; Egypt is what we\'ve gone deepest on first.' }}
            </p>
        </div>
    </section>

    <section class="eg-three" id="how">
        <div class="wrap">
            <div style="text-align: center; max-width: 36rem; margin: 0 auto 2.5rem;">
                <span class="eyebrow">{{ $isAr ? 'الفرق' : 'The difference' }}</span>
                <h2 class="display-serif" style="font-size: clamp(28px, 4vw, 38px); margin-top: 0.75rem;">
                    {{ $isAr
                        ? 'ثلاث حاجات الأدوات الأخرى ما تعملهاش'
                        : 'Three things other tools don\'t do' }}
                </h2>
            </div>

            <div class="eg-three-grid">
                <div class="eg-three-card">
                    <span class="num">01</span>
                    <h3>{{ $isAr ? 'استشهادات موثقة' : 'Verified citations' }}</h3>
                    <p>
                        {{ $isAr
                            ? 'كل "المادة ٧٤ من قانون الشركات" يتم التحقق منها قبل ما توصلك. ما لقتلكش مطابقة في المكتبة؟ بيتعلَّم بالأحمر. لا اختلاق، لا مفاجآت.'
                            : 'Every "Article 74 of the Companies Law" is verified against a live corpus of 124 MENA statutes before it reaches you. Unverified citations are flagged red. No fabricated articles slipping into a signed contract.' }}
                    </p>
                </div>

                <div class="eg-three-card">
                    <span class="num">02</span>
                    <h3>{{ $isAr ? 'عربي مُلزم، إنجليزي للعمل' : 'Arabic binding, English working' }}</h3>
                    <p>
                        {{ $isAr
                            ? 'مكتبة من ٦٠٠ مصطلح مدني-قانوني. "الفسخ" تترجم rescission مش termination. تصدير جنب بجنب في Word أو PDF.'
                            : 'A 600-term civil-law glossary pinned into the translator. الفسخ → rescission (not "termination"). Side-by-side .docx or .pdf in one click.' }}
                    </p>
                </div>

                <div class="eg-three-card">
                    <span class="num">03</span>
                    <h3>{{ $isAr ? 'سعر مصري' : 'Egyptian pricing' }}</h3>
                    <p>
                        {{ $isAr
                            ? '٩٩٩ جنيه شهرياً للباقة الفردية. أقل من ساعة عمل لمحامي كبير. أقل من ١٠ صفحات ترجمة محترفة. أقل من ربع راتب زميل جديد.'
                            : 'Solo plan starts at EGP 999/month. Less than one billable hour. Less than 10 pages of outsourced translation. Less than 1/8 of a junior associate\'s monthly cost.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="eg-trust">
        <div class="wrap">
            <div style="text-align: center;">
                <span class="eyebrow">{{ $isAr ? 'في أرقام' : 'In numbers' }}</span>
            </div>
            <div class="trust-row">
                <div class="trust-item">
                    <div class="big">١٢٤</div>
                    <div class="lbl">{{ $isAr ? 'قانون في المكتبة' : 'statutes in corpus' }}</div>
                </div>
                <div class="trust-item">
                    <div class="big">١١</div>
                    <div class="lbl">{{ $isAr ? 'دولة عربية' : 'MENA jurisdictions' }}</div>
                </div>
                <div class="trust-item">
                    <div class="big">٢٥٠+</div>
                    <div class="lbl">{{ $isAr ? 'مادة قانونية مفهرسة' : 'articles indexed' }}</div>
                </div>
                <div class="trust-item">
                    <div class="big">١٤ {{ $isAr ? 'يوم' : 'day' }}</div>
                    <div class="lbl">{{ $isAr ? 'تجربة مجانية بدون بطاقة' : 'free trial, no card' }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="eg-pricing">
        <div class="wrap-narrow">
            <div style="text-align: center; margin-bottom: 2.5rem;">
                <span class="eyebrow">{{ $isAr ? 'السعر' : 'Pricing' }}</span>
                <h2 class="display-serif" style="font-size: clamp(28px, 4vw, 38px); margin-top: 0.75rem;">
                    {{ $isAr ? 'باقة مصرية، مفيش رسوم خفية' : 'An Egyptian plan, no hidden charges' }}
                </h2>
            </div>

            <div class="eg-price-card">
                <span class="label">{{ $isAr ? 'باقة الفرد · مصر' : 'Starter · Egypt' }}</span>
                <p class="price">
                    {{ $isAr ? '٩٩٩' : 'EGP 999' }}<span class="unit">{{ $isAr ? ' ج.م/شهر' : '/month' }}</span>
                </p>
                <p class="sub">
                    {{ $isAr
                        ? 'أقل من ساعة عمل لمحامي كبير. التجربة ١٤ يوم مجاناً.'
                        : 'Less than one billable hour. 14-day free trial included.' }}
                </p>

                <ul>
                    <li>{{ $isAr ? '٢٥ عقد شهرياً' : '25 contracts per month' }}</li>
                    <li>{{ $isAr ? 'تعديلات غير محدودة لكل مسودة' : 'Unlimited edits per draft' }}</li>
                    <li>{{ $isAr ? 'تصدير ثنائي اللغة بدون علامة مائية' : 'Bilingual export, no watermark' }}</li>
                    <li>{{ $isAr ? 'سجل المراجعات الكامل (١٠ إصدارات/عقد)' : 'Full revision history (10 versions per contract)' }}</li>
                    <li>{{ $isAr ? 'استيراد العقد المضاد ومقارنته' : 'Counter-proposal paste + diff' }}</li>
                    <li>{{ $isAr ? 'لوحة استشهادات موثقة' : 'Citation audit panel' }}</li>
                    <li>{{ $isAr ? 'دعم عبر البريد الإلكتروني (٢٤ ساعة)' : 'Email support (24-hour response)' }}</li>
                </ul>

                <a href="{{ route('register') }}" class="btn-primary" style="width: 100%; justify-content: center;">
                    {{ $isAr ? 'ابدأ التجربة المجانية' : 'Start 14-day free trial' }}
                </a>
                <p style="margin-top: 0.75rem; font-size: 12px; color: var(--ink-mute); text-align: center;">
                    {{ $isAr ? 'بدون بطاقة. ألغي وقتما تشاء.' : 'No card required. Cancel anytime.' }}
                </p>
            </div>

            <p style="text-align: center; margin-top: 2rem; font-size: 13.5px; color: var(--ink-soft);">
                {{ $isAr ? 'تحتاج أكثر من ٢٥ عقد/شهر؟' : 'Need more than 25 contracts/month?' }}
                <a href="{{ route('marketing.pricing') }}" style="text-decoration: underline;">{{ $isAr ? 'اطلع على باقة Firm' : 'See the Firm plan' }}</a>
            </p>
        </div>
    </section>

    <section class="eg-cta">
        <div class="wrap-narrow">
            <h2>
                {{ $isAr
                    ? 'وفّر ويك اند الشهر ده'
                    : 'Get your weekend back this month' }}
            </h2>
            <p class="lede" style="margin: 0 auto;">
                {{ $isAr
                    ? '١٤ يوم تجربة مجانية، بدون بطاقة. لو لقيتها مفيدة، اشترك. لو لأ، أنت مش خسران شيء.'
                    : '14 days, free, no credit card. If it saves you time, subscribe. If not, you\'ve lost nothing.' }}
            </p>
            <div style="margin-top: 2rem;">
                <a href="{{ route('register') }}" class="btn-primary">{{ $isAr ? 'ابدأ الآن' : 'Start now' }}</a>
            </div>
        </div>
    </section>
</x-marketing.layout>
