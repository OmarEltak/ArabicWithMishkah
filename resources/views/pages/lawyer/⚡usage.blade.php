<?php

use App\Services\AI\UsageTracker;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Usage & cost')] class extends Component {
    #[Computed]
    public function dashboard(): array
    {
        return app(UsageTracker::class)->dashboard();
    }
}; ?>

<div class="mx-auto w-full max-w-7xl px-6 py-8">
    @php($d = $this->dashboard)
    @php($globalPct = $d['today_global_budget_usd'] > 0 ? min(100, $d['today_global_usd'] / $d['today_global_budget_usd'] * 100) : 0)
    @php($userPct = $d['today_user_budget_usd'] > 0 ? min(100, $d['today_user_usd'] / $d['today_user_budget_usd'] * 100) : 0)

    @php($isFirstRun = count($d['recent']) === 0 && (float) $d['today_user_usd'] === 0.0 && (float) $d['month_global_usd'] === 0.0)

    {{-- Header --}}
    <div class="border-b hairline pb-6">
        <span class="eyebrow-tag">{{ __('Operations') }}</span>
        <h1 class="display mt-2 text-3xl text-zinc-900 dark:text-zinc-50">{{ __('Usage & cost') }}</h1>
        <p class="mt-1 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Per-call cost tracking across Anthropic, Voyage, OpenAI, and Groq. Daily caps enforced before every paid request.') }}
        </p>
    </div>

    @if ($isFirstRun)
        <div class="mt-6 rounded-lg border border-dashed hairline p-8 md:p-10">
            <div class="grid gap-6 md:grid-cols-[1.4fr_1fr] md:items-center">
                <div>
                    <span class="eyebrow-tag">{{ __('Nothing here yet') }}</span>
                    <h2 class="display mt-3 text-xl text-zinc-900 dark:text-zinc-50 md:text-2xl">
                        {{ __('Your usage will appear after your first draft') }}
                    </h2>
                    <p class="mt-2 max-w-md text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                        {{ __('Every AI call (clarifying turns, drafting, translation, citation verification) is logged with token counts and a USD estimate. Daily and per-call caps prevent surprise bills.') }}
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <flux:button :href="route('lawyer.chat')" wire:navigate variant="primary" icon="pencil-square">
                            {{ __('Start a draft') }}
                        </flux:button>
                        <flux:button :href="route('lawyer.knowledge')" wire:navigate variant="ghost" icon="building-library">
                            {{ __('Browse knowledge base') }}
                        </flux:button>
                    </div>
                </div>
                <ul class="space-y-2.5 border-l hairline ps-5 text-sm text-zinc-600 dark:text-zinc-400">
                    <li class="flex gap-2"><span class="text-zinc-400">→</span> {{ __('Daily cap (you):') }} <span class="ms-auto tabular-nums">${{ number_format($d['today_user_budget_usd'], 2) }}</span></li>
                    <li class="flex gap-2"><span class="text-zinc-400">→</span> {{ __('Daily cap (global):') }} <span class="ms-auto tabular-nums">${{ number_format($d['today_global_budget_usd'], 2) }}</span></li>
                    <li class="flex gap-2"><span class="text-zinc-400">→</span> {{ __('Providers tracked:') }} <span class="ms-auto font-mono text-xs">Anthropic, Gemini, Groq, Voyage</span></li>
                </ul>
            </div>
        </div>
    @endif

    {{-- Spend cards --}}
    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <div class="card p-5">
            <div class="flex items-baseline justify-between">
                <span class="eyebrow-tag">{{ __("Today (you)") }}</span>
                <span class="text-[10px] text-zinc-400">{{ __('cap') }} ${{ number_format($d['today_user_budget_usd'], 2) }}</span>
            </div>
            <p class="display mt-3 text-3xl text-zinc-900 dark:text-zinc-50 tabular-nums">${{ number_format($d['today_user_usd'], 4) }}</p>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                <div class="h-full rounded-full transition-all"
                    style="width: {{ $userPct }}%; background: {{ $userPct >= 90 ? 'var(--color-danger)' : ($userPct >= 60 ? 'var(--color-warning)' : 'var(--color-success)') }}"></div>
            </div>
            <p class="mt-2 text-xs text-zinc-500 tabular-nums">{{ number_format($userPct, 0) }}% {{ __('of cap') }}</p>
        </div>

        <div class="card p-5">
            <div class="flex items-baseline justify-between">
                <span class="eyebrow-tag">{{ __('Today (global)') }}</span>
                <span class="text-[10px] text-zinc-400">{{ __('cap') }} ${{ number_format($d['today_global_budget_usd'], 2) }}</span>
            </div>
            <p class="display mt-3 text-3xl text-zinc-900 dark:text-zinc-50 tabular-nums">${{ number_format($d['today_global_usd'], 4) }}</p>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                <div class="h-full rounded-full transition-all"
                    style="width: {{ $globalPct }}%; background: {{ $globalPct >= 90 ? 'var(--color-danger)' : ($globalPct >= 60 ? 'var(--color-warning)' : 'var(--color-success)') }}"></div>
            </div>
            <p class="mt-2 text-xs text-zinc-500 tabular-nums">{{ number_format($globalPct, 0) }}% {{ __('of cap') }}</p>
        </div>

        <div class="card p-5">
            <span class="eyebrow-tag">{{ __('Month-to-date (global)') }}</span>
            <p class="display mt-3 text-3xl text-zinc-900 dark:text-zinc-50 tabular-nums">${{ number_format($d['month_global_usd'], 2) }}</p>
            <p class="mt-2 text-xs text-zinc-500">{{ __('Resets on the 1st') }}</p>
        </div>
    </div>

    {{-- Recent calls --}}
    <div class="mt-10">
        <div class="mb-4 flex items-baseline justify-between">
            <span class="eyebrow-tag">{{ __('Recent calls') }}</span>
            <p class="text-xs text-zinc-500">{{ __('Last 20') }}</p>
        </div>
        @if (count($d['recent']) === 0)
            <div class="rounded-md border border-dashed hairline p-12 text-center">
                <p class="text-sm text-zinc-500">{{ __('No AI calls recorded yet.') }}</p>
            </div>
        @else
            <div class="overflow-hidden rounded-lg border hairline bg-white dark:bg-zinc-900">
                <table class="w-full text-sm">
                    <thead class="border-b hairline bg-zinc-50/50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-950/30">
                        <tr>
                            <th class="px-4 py-3 text-start font-medium">{{ __('When') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Provider') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Op') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Model') }}</th>
                            <th class="px-4 py-3 text-end font-medium">{{ __('In') }}</th>
                            <th class="px-4 py-3 text-end font-medium">{{ __('Out') }}</th>
                            <th class="px-4 py-3 text-end font-medium">{{ __('Cost (USD)') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($d['recent'] as $r)
                            <tr class="border-t hairline {{ $r['status'] === 'error' ? 'bg-[var(--color-danger-soft)]/30' : '' }}">
                                <td class="px-4 py-3 text-zinc-500">{{ $r['when'] }}</td>
                                <td class="px-4 py-3"><span class="pill pill-neutral">{{ $r['provider'] }}</span></td>
                                <td class="px-4 py-3 font-mono text-xs text-zinc-700 dark:text-zinc-300">{{ $r['op'] }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-zinc-600 dark:text-zinc-400">{{ $r['model'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-end font-mono text-xs text-zinc-700 dark:text-zinc-300 tabular-nums">{{ $r['in'] !== null ? number_format($r['in']) : '—' }}</td>
                                <td class="px-4 py-3 text-end font-mono text-xs text-zinc-700 dark:text-zinc-300 tabular-nums">{{ $r['out'] !== null ? number_format($r['out']) : '—' }}</td>
                                <td class="px-4 py-3 text-end font-mono text-xs text-zinc-900 dark:text-zinc-100 tabular-nums">{{ number_format($r['cost'], 6) }}</td>
                                <td class="px-4 py-3">
                                    @if ($r['status'] === 'success')
                                        <span class="pill pill-success">{{ $r['status'] }}</span>
                                    @else
                                        <span class="pill pill-danger">{{ $r['status'] }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
