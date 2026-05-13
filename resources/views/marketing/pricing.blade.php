<?php
$locale = app()->getLocale();
$isAr = $locale === 'ar';
$canonical = url('/pricing');

$pageTitle = $isAr
    ? 'الأسعار · My-lawyer · صياغة العقود بالذكاء الاصطناعي'
    : 'Pricing · My-lawyer · AI contract drafting';
$pageDescription = $isAr
    ? 'خطط My-lawyer للأفراد والشركات ومكاتب المحاماة في الشرق الأوسط. ابدأ مجاناً، أو ارفع خطتك لاستخدام غير محدود.'
    : 'Plans for solo lawyers, firms, and corporates across MENA. Start free, upgrade for unlimited drafting.';

$plans = config('lawyer.plans', []);

$jsonLd = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => 'My-lawyer',
    'description' => $pageDescription,
    'offers' => array_map(function ($slug, $p) {
        return [
            '@type' => 'Offer',
            'name' => $p['name_en'],
            'price' => $p['price_monthly_egp'] ?? '0',
            'priceCurrency' => 'EGP',
            'availability' => 'https://schema.org/InStock',
        ];
    }, array_keys($plans), $plans),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>

<x-marketing.layout
    :page-title="$pageTitle"
    :page-description="$pageDescription"
    :canonical="$canonical"
    :json-ld="$jsonLd"
>

{{-- Hero --}}
<section class="section">
    <div class="wrap-narrow" style="text-align: center;">
        <span class="badge-tag">
            <span class="pip"></span>{{ __('Pricing') }}
        </span>
        <h1 class="display-serif" style="font-size: clamp(36px, 5.4vw, 60px); margin: 1rem auto 1.25rem; max-width: 18ch;">
            @if ($isAr)
                خطط <span style="color: var(--navy);">واضحة</span>، بدون مفاجآت.
            @else
                Plans built for <span style="color: var(--navy);">how lawyers work</span>.
            @endif
        </h1>
        <p class="lede" style="margin: 0 auto;">
            @if ($isAr)
                ابدأ مجاناً مع ٣ مسودات شهرياً. اشترك عندما تصبح المنصة جزءاً من سير عملك. لا عقود ملزمة لفترات طويلة.
            @else
                Start free with 3 drafts a month. Upgrade when the platform earns a place in your workflow. No long-term lock-ins.
            @endif
        </p>
    </div>
</section>

{{-- Plan grid --}}
<section style="padding-bottom: clamp(3rem, 6vw, 5rem);">
    <div class="wrap">
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); max-width: 1180px; margin: 0 auto;">
            @foreach ($plans as $slug => $p)
                @php($price = $p['price_monthly_egp'])
                @php($highlighted = $p['highlighted'] ?? false)
                <div style="
                    padding: 1.75rem 1.5rem;
                    background: {{ $highlighted ? 'var(--navy)' : 'var(--canvas)' }};
                    color: {{ $highlighted ? '#fff' : 'var(--ink)' }};
                    border: 1px solid {{ $highlighted ? 'var(--navy)' : 'var(--hairline)' }};
                    border-radius: 14px;
                    display: flex; flex-direction: column;
                    position: relative;
                    {{ $highlighted ? 'box-shadow: 0 12px 36px -16px rgba(15, 41, 66, 0.35);' : '' }}
                ">
                    @if ($highlighted)
                        <span style="
                            position: absolute; top: -10px; inset-inline-start: 1.5rem;
                            background: #FCD34D; color: #78350F;
                            font-family: var(--f-mono); font-size: 10px; font-weight: 700;
                            letter-spacing: 0.1em; text-transform: uppercase;
                            padding: 0.25rem 0.6rem; border-radius: 4px;
                        ">
                            {{ __('Most popular') }}
                        </span>
                    @endif

                    {{-- Name --}}
                    <p style="font-family: var(--f-mono); font-size: 11px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: {{ $highlighted ? 'rgba(255,255,255,0.72)' : 'var(--ink-mute)' }}; margin: 0 0 0.6rem;">
                        {{ $isAr ? $p['name_ar'] : $p['name_en'] }}
                    </p>

                    {{-- Price --}}
                    <div style="margin-bottom: 1.5rem; min-height: 60px;">
                        @if ($price === null)
                            <p style="font-family: var(--f-serif); font-size: 28px; font-weight: 500; letter-spacing: -0.02em; margin: 0;">
                                {{ __('Custom') }}
                            </p>
                            <p style="font-size: 13px; color: {{ $highlighted ? 'rgba(255,255,255,0.72)' : 'var(--ink-soft)' }}; margin: 0.25rem 0 0;">
                                {{ __('Contact sales for a quote') }}
                            </p>
                        @else
                            <p style="font-family: var(--f-serif); font-size: 36px; font-weight: 500; letter-spacing: -0.025em; margin: 0; line-height: 1;">
                                {{ number_format($price) }}
                                <span style="font-size: 13px; font-weight: 400; color: {{ $highlighted ? 'rgba(255,255,255,0.65)' : 'var(--ink-mute)' }}; font-family: var(--f-sans);">
                                    {{ __('EGP / month') }}
                                </span>
                            </p>
                            @if ($p['price_yearly_egp'] && $p['price_yearly_egp'] < $price * 12)
                                <p style="font-size: 12px; color: {{ $highlighted ? 'rgba(255,255,255,0.65)' : 'var(--ink-mute)' }}; margin: 0.35rem 0 0;">
                                    {{ __('or :y EGP / year (save :s%)', [
                                        'y' => number_format($p['price_yearly_egp']),
                                        's' => (int) round(100 - ($p['price_yearly_egp'] / ($price * 12)) * 100),
                                    ]) }}
                                </p>
                            @endif
                        @endif
                    </div>

                    {{-- Features --}}
                    <ul style="list-style: none; padding: 0; margin: 0 0 1.5rem; flex: 1; display: flex; flex-direction: column; gap: 0.65rem;">
                        @foreach (($isAr ? $p['features']['ar'] : $p['features']['en']) as $feature)
                            <li style="display: flex; gap: 0.55rem; font-size: 13.5px; line-height: 1.5; color: {{ $highlighted ? 'rgba(255,255,255,0.88)' : 'var(--ink-soft)' }};">
                                <span style="color: {{ $highlighted ? '#7DD3A5' : 'var(--green)' }}; flex-shrink: 0;">✓</span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    {{-- CTA --}}
                    @if ($slug === 'enterprise')
                        <a href="mailto:sales@my-lawyer.com" style="
                            display: inline-flex; align-items: center; justify-content: center;
                            padding: 0.75rem 1.2rem;
                            background: {{ $highlighted ? '#fff' : 'var(--canvas)' }};
                            color: var(--ink);
                            border: 1px solid {{ $highlighted ? '#fff' : 'var(--hairline)' }};
                            border-radius: 10px;
                            font-size: 14px; font-weight: 600;
                            text-decoration: none;
                            transition: background 0.15s;
                        ">
                            {{ $isAr ? $p['cta_ar'] : $p['cta_en'] }}
                        </a>
                    @elseif ($slug === 'free')
                        <a href="{{ route('register') }}" class="btn-{{ $highlighted ? 'outline' : 'primary' }}" style="
                            justify-content: center;
                            {{ $highlighted ? 'background: #fff; color: var(--navy); border-color: #fff;' : '' }}
                        ">
                            {{ $isAr ? $p['cta_ar'] : $p['cta_en'] }}
                        </a>
                    @else
                        <a href="{{ route('register') }}?plan={{ $slug }}" style="
                            display: inline-flex; align-items: center; justify-content: center;
                            padding: 0.85rem 1.4rem;
                            background: {{ $highlighted ? '#fff' : 'var(--navy)' }};
                            color: {{ $highlighted ? 'var(--navy)' : '#fff' }};
                            border: 1px solid {{ $highlighted ? '#fff' : 'var(--navy)' }};
                            border-radius: 10px;
                            font-size: 14px; font-weight: 600;
                            text-decoration: none;
                            transition: opacity 0.15s;
                        ">
                            {{ $isAr ? $p['cta_ar'] : $p['cta_en'] }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- VAT + trial notice --}}
        <p style="text-align: center; margin-top: 2rem; font-size: 12.5px; color: var(--ink-mute);">
            {{ __('Prices in Egyptian Pounds (EGP). VAT may apply depending on jurisdiction. Solo and Firm plans include a 14-day free trial.') }}
        </p>
    </div>
</section>

{{-- FAQ-ish row --}}
<section class="section section-surface">
    <div class="wrap">
        <div style="display: grid; gap: 2.5rem; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
            <div>
                <h3 style="font-family: var(--f-serif); font-size: 19px; font-weight: 600; color: var(--ink); margin: 0 0 0.65rem;">
                    {{ __('Can I cancel anytime?') }}
                </h3>
                <p style="font-size: 14px; line-height: 1.65; color: var(--ink-soft); margin: 0;">
                    {{ __('Yes. Cancel from your billing settings — your plan stays active until the end of the current period, then drops to Free. No call-to-cancel friction.') }}
                </p>
            </div>
            <div>
                <h3 style="font-family: var(--f-serif); font-size: 19px; font-weight: 600; color: var(--ink); margin: 0 0 0.65rem;">
                    {{ __('What payment methods do you accept?') }}
                </h3>
                <p style="font-size: 14px; line-height: 1.65; color: var(--ink-soft); margin: 0;">
                    {{ __('Major credit cards (Visa / Mastercard) globally via Stripe. Local cards and bank transfer in Egypt via Tap. Enterprise: invoice on net-30 terms.') }}
                </p>
            </div>
            <div>
                <h3 style="font-family: var(--f-serif); font-size: 19px; font-weight: 600; color: var(--ink); margin: 0 0 0.65rem;">
                    {{ __('Do drafts I generated on a paid plan stay accessible if I downgrade?') }}
                </h3>
                <p style="font-size: 14px; line-height: 1.65; color: var(--ink-soft); margin: 0;">
                    {{ __('Yes. All your past drafts and exports remain in your account. Only your monthly new-draft quota changes with your plan.') }}
                </p>
            </div>
            <div>
                <h3 style="font-family: var(--f-serif); font-size: 19px; font-weight: 600; color: var(--ink); margin: 0 0 0.65rem;">
                    {{ __('Can I get a custom plan for my firm?') }}
                </h3>
                <p style="font-size: 14px; line-height: 1.65; color: var(--ink-soft); margin: 0;">
                    {{ __('For firms above 5 seats or with specific compliance / self-hosting needs, contact sales@my-lawyer.com — we put together quotes within 48 hours.') }}
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Final CTA --}}
<section class="section" style="text-align: center; border-top: 1px solid var(--hairline);">
    <div class="wrap-narrow">
        <h2 class="display-serif" style="font-size: clamp(28px, 4vw, 44px); margin: 0;">
            {{ __('Start drafting in two minutes.') }}
        </h2>
        <p class="lede" style="margin: 1.25rem auto 2rem;">
            {{ __('No credit card required for Free. Solo and Firm trials are 14 days, cancel before billing if it is not for you.') }}
        </p>
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.85rem;">
            <a href="{{ route('register') }}" class="btn-primary">{{ __('Start free') }}</a>
            <a href="{{ route('home') }}#preview" class="btn-outline">{{ __('See sample output') }}</a>
        </div>
    </div>
</section>

</x-marketing.layout>
