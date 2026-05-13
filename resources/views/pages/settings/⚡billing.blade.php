<?php

use App\Services\Contracts\PlanUsageGate;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Billing')] class extends Component {

    #[Computed]
    public function usage(): array
    {
        return app(PlanUsageGate::class)->snapshot(Auth::user());
    }

    #[Computed]
    public function planMeta(): array
    {
        $slug = $this->usage['plan'] ?? 'free';
        return (array) config("lawyer.plans.{$slug}", []);
    }

    #[Computed]
    public function periodResetsOn(): ?string
    {
        $start = $this->usage['period_start'] ?? null;
        if (! $start) {
            return null;
        }
        return \Carbon\Carbon::parse($start)->addDays(30)->toFormattedDateString();
    }
};
?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout
        :heading="__('Billing')"
        :subheading="__('Your plan, usage this period, and payment management.')"
    >
        @php($u = $this->usage)
        @php($p = $this->planMeta)
        @php($cap = $u['limit'])
        @php($used = $u['used'])
        @php($pct = $cap ? min(100, (int) round($used / max(1, $cap) * 100)) : 0)

        <div class="space-y-6">

            {{-- Current plan card --}}
            <div class="rounded-xl border hairline bg-white p-5 dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-mono text-[11px] uppercase tracking-[0.12em] text-zinc-500">{{ __('Current plan') }}</p>
                        <p class="font-serif text-2xl font-medium tracking-tight mt-1">
                            {{ app()->getLocale() === 'ar' ? ($p['name_ar'] ?? 'مجاني') : ($p['name_en'] ?? 'Free') }}
                        </p>
                        @if (! empty($p['price_monthly_egp']))
                            <p class="text-sm text-zinc-500 mt-0.5">{{ number_format($p['price_monthly_egp']) }} {{ __('EGP / month') }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        @if (Auth::user()->plan === 'free')
                            <flux:button :href="route('marketing.pricing')" wire:navigate variant="primary" size="sm" icon="arrow-up-right">{{ __('Upgrade') }}</flux:button>
                        @else
                            <flux:button :href="route('billing.portal')" variant="ghost" size="sm" icon="cog">{{ __('Manage subscription') }}</flux:button>
                            <flux:button :href="route('marketing.pricing')" wire:navigate variant="ghost" size="sm">{{ __('Change plan') }}</flux:button>
                        @endif
                    </div>
                </div>

                {{-- Usage bar --}}
                @if ($cap !== null)
                    <div class="mt-5">
                        <div class="flex items-baseline justify-between mb-1.5">
                            <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ __('Drafts this period') }}</span>
                            <span class="font-mono text-xs tabular-nums {{ $used >= $cap ? 'text-rose-600' : 'text-zinc-500' }}">
                                {{ $used }} / {{ $cap }}
                            </span>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                            <div class="h-full rounded-full {{ $used >= $cap ? 'bg-rose-500' : 'bg-[var(--color-accent)]' }}" style="width: {{ $pct }}%;"></div>
                        </div>
                        @if ($this->periodResetsOn)
                            <p class="mt-2 text-[11px] text-zinc-500">
                                {{ __('Counter resets on :date.', ['date' => $this->periodResetsOn]) }}
                            </p>
                        @endif
                        @if ($used >= $cap)
                            <p class="mt-2 text-xs text-rose-700 dark:text-rose-300">
                                {{ __("You've reached this period's draft limit.") }}
                                <a href="{{ route('marketing.pricing') }}" wire:navigate class="font-semibold underline">{{ __('Upgrade to keep drafting.') }}</a>
                            </p>
                        @endif
                    </div>
                @else
                    <p class="mt-4 text-sm text-zinc-500">{{ __('Unlimited drafts on this plan.') }}</p>
                @endif
            </div>

            {{-- Payment method (only when subscribed) --}}
            @if (Auth::user()->stripe_id)
                <div class="rounded-xl border hairline bg-white p-5 dark:bg-zinc-900">
                    <p class="font-mono text-[11px] uppercase tracking-[0.12em] text-zinc-500">{{ __('Payment method') }}</p>
                    @if (Auth::user()->pm_type && Auth::user()->pm_last_four)
                        <p class="mt-2">
                            {{ ucfirst(Auth::user()->pm_type) }} •••• {{ Auth::user()->pm_last_four }}
                        </p>
                    @else
                        <p class="mt-2 text-sm text-zinc-500">{{ __('No payment method on file.') }}</p>
                    @endif
                </div>
            @endif

            {{-- Compare plans link --}}
            <div class="rounded-xl border hairline bg-zinc-50/50 p-5 dark:bg-zinc-950/30">
                <p class="font-serif text-base font-medium">{{ __('Need more drafts, more seats, or self-hosted?') }}</p>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Compare every plan side-by-side, or contact sales for Enterprise.') }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <flux:button :href="route('marketing.pricing')" wire:navigate variant="primary" size="sm">{{ __('Compare plans') }}</flux:button>
                    <flux:button href="mailto:sales@my-lawyer.com" variant="ghost" size="sm">{{ __('Contact sales') }}</flux:button>
                </div>
            </div>

            {{-- Audit-friendly disclosure --}}
            <p class="text-[11px] text-zinc-500 leading-relaxed">
                {{ __('Invoices and payment receipts are stored on file for tax purposes for 7 years per Egyptian Commercial Code Article 47. Your billing history is accessible from the Manage subscription portal.') }}
            </p>

        </div>
    </x-settings.layout>
</section>
