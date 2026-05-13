@props([
    'iso',
    'size' => 'sm',
])
@php
    $level = config('coverage.jurisdictions.'.$iso, 'preview');
    $cfg = config('coverage.levels.'.$level);
    $isAr = app()->getLocale() === 'ar';
    $label = $isAr ? ($cfg['short_ar'] ?? $cfg['label_ar']) : ($cfg['short_en'] ?? $cfg['label_en']);
    $tone = $cfg['tone'] ?? 'gray';

    $bg = match ($tone) {
        'green' => '#DDF1E5',
        'amber' => '#FBF1D8',
        default => '#E9EDF2',
    };
    $fg = match ($tone) {
        'green' => '#0E5A36',
        'amber' => '#7A5A0F',
        default => '#4B5868',
    };
    $sizeStyles = match ($size) {
        'lg' => 'font-size: 11px; padding: 0.3rem 0.7rem;',
        default => 'font-size: 9.5px; padding: 0.18rem 0.5rem;',
    };
@endphp
<span
    title="{{ $isAr ? ($cfg['note_ar'] ?? '') : ($cfg['note_en'] ?? '') }}"
    style="display: inline-flex; align-items: center; gap: 0.3rem; {{ $sizeStyles }} font-family: var(--f-mono); font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; background: {{ $bg }}; color: {{ $fg }}; border-radius: 4px;"
>
    @if ($tone === 'green')
        <svg width="8" height="8" viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="3"/></svg>
    @endif
    {{ $label }}
</span>
