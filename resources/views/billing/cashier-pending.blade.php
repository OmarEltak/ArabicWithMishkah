<x-marketing.layout
    :page-title="__('Setup pending · My-lawyer')"
    :page-description="__('Cashier not yet installed')"
>
<section class="section">
    <div class="wrap-narrow" style="text-align: center;">
        <span class="badge-tag">
            <span class="pip"></span>{{ __('Cashier pending') }}
        </span>
        <h1 class="display-serif" style="font-size: clamp(28px, 4vw, 44px); margin: 1.25rem auto 1rem;">
            {{ __('Almost ready.') }}
        </h1>
        <p class="lede" style="margin: 0 auto 2rem;">
            {{ $message ?? __('Cashier is being installed. See docs/BILLING.md for the developer setup steps.') }}
        </p>
        <a href="{{ route('marketing.pricing') }}" class="btn-primary">{{ __('Back to pricing') }}</a>
    </div>
</section>
</x-marketing.layout>
