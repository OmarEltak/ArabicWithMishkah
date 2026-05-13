@props([
    'type' => 'button',
    'variant' => 'primary',     // primary | ghost | danger
    'size' => 'sm',             // sm | md | xs
    'target' => null,           // wire:target value — defaults to wire:click attr
    'wireClick' => null,        // optional wire:click; we'll also use it as the default target
    'wireSubmit' => null,
    'icon' => null,
    'iconAlt' => null,
    'disabled' => false,
    'fullWidth' => false,
    'busyLabel' => null,
])

@php
    // Resolve the loading-target string: priority is explicit target > wire:click >
    // wire:submit > nothing (then we just rely on $disabled).
    $resolvedTarget = $target ?? $wireClick ?? $wireSubmit;

    $base = 'lb-base';
    $sizeClass = match($size) {
        'xs' => 'lb-xs',
        'md' => 'lb-md',
        default => 'lb-sm',
    };
    $variantClass = match($variant) {
        'ghost' => 'lb-ghost',
        'danger' => 'lb-danger',
        default => 'lb-primary',
    };
    $widthClass = $fullWidth ? 'w-full justify-center' : '';
@endphp

<button
    type="{{ $type }}"
    @if ($wireClick) wire:click="{{ $wireClick }}" @endif
    @if ($disabled) disabled @endif
    {{ $attributes->class([$base, $sizeClass, $variantClass, $widthClass]) }}
>
    {{-- Idle content --}}
    <span
        class="lb-content"
        @if ($resolvedTarget) wire:loading.remove wire:target="{{ $resolvedTarget }}" @endif
    >
        @if ($icon)
            {{-- Use the dynamic flux icon component (resolves at runtime to
                 <flux:icon.{name}>) so consumers can pass icon="plus" /
                 "arrow-right" / "sparkles" etc. without us hard-coding a
                 variant attribute that flux:icon doesn't accept. --}}
            @php($iconComponent = 'flux::icon.'.$icon)
            <x-dynamic-component :component="'flux::icon.'.$icon" class="lb-icon" />
        @endif
        <span class="lb-label">{{ $slot }}</span>
    </span>

    {{-- Loading content. Shows wherever wire:target matches. --}}
    @if ($resolvedTarget)
        <span
            class="lb-content lb-busy"
            wire:loading.flex
            wire:target="{{ $resolvedTarget }}"
        >
            <span class="lb-spinner" aria-hidden="true"></span>
            <span class="lb-label">{{ $busyLabel ?? __('Working…') }}</span>
        </span>
    @endif
</button>
