@props([
    'sidebar' => false,
    'href' => null,
])

@php
    // Editorial wordmark with a serif numeral §. Two characters of personality
    // that read instantly as "legal" without leaning on a stock briefcase icon.
    $brand = 'My-lawyer';
@endphp

@if($sidebar)
    <a href="{{ $href ?? route('dashboard') }}" wire:navigate class="flex items-center gap-3 px-1 py-2">
        <span class="flex aspect-square size-9 items-center justify-center rounded-md text-white shadow-[inset_0_-2px_0_rgba(0,0,0,0.15)]" style="background: var(--surface-deep)">
            <span class="font-serif text-lg leading-none">§</span>
        </span>
        <span class="flex flex-col leading-tight">
            <span class="font-serif text-base text-zinc-900 dark:text-zinc-50">{{ $brand }}</span>
            <span class="text-[10px] uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">{{ __('Drafting suite') }}</span>
        </span>
    </a>
@else
    <a href="{{ $href ?? route('dashboard') }}" wire:navigate class="inline-flex items-center gap-3">
        <span class="flex aspect-square size-9 items-center justify-center rounded-md text-white" style="background: var(--surface-deep)">
            <span class="font-serif text-lg leading-none">§</span>
        </span>
        <span class="font-serif text-lg text-zinc-900 dark:text-zinc-50">{{ $brand }}</span>
    </a>
@endif
