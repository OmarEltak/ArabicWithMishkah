<x-marketing.layout
    :page-title="__('Billing not yet configured · My-lawyer')"
    :page-description="__('Billing is being set up')"
>
<section class="section">
    <div class="wrap-narrow" style="text-align: center;">
        <span class="badge-tag">
            <span class="pip"></span>{{ __('Billing setup pending') }}
        </span>
        <h1 class="display-serif" style="font-size: clamp(28px, 4vw, 44px); margin: 1.25rem auto 1rem;">
            {{ __('Billing is not yet configured.') }}
        </h1>
        <p class="lede" style="margin: 0 auto 2rem;">
            {{ $reason ?? __('We are finishing the payment provider integration. Please check back soon, or contact sales@my-lawyer.com for early access.') }}
        </p>
        <div style="display: flex; gap: 0.85rem; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('marketing.pricing') }}" class="btn-outline">{{ __('Back to pricing') }}</a>
            <a href="mailto:sales@my-lawyer.com" class="btn-primary">{{ __('Contact sales') }}</a>
        </div>
    </div>
</section>
</x-marketing.layout>
