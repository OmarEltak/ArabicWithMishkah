@php
    $announcement = (array) config('lawyer.announcement', []);
    $body = trim((string) ($announcement['body'] ?? ''));
@endphp

@if ($body !== '')
    @php
        $id = (string) ($announcement['id'] ?? 'default');
        $level = (string) ($announcement['level'] ?? 'info');
        $link = $announcement['link'] ?? null;
        $linkLabel = $announcement['link_label'] ?? null;
        $colors = match ($level) {
            'success' => ['bg' => '#DDF1E5', 'border' => '#A6D9BB', 'fg' => '#0F4A2A', 'dot' => '#156B40'],
            'warning' => ['bg' => '#FEF3C7', 'border' => '#FDE68A', 'fg' => '#78350F', 'dot' => '#B8932F'],
            'danger' => ['bg' => '#FEE2E2', 'border' => '#FCA5A5', 'fg' => '#7F1D1D', 'dot' => '#B91C1C'],
            default => ['bg' => '#E1E9F6', 'border' => '#B7C8E4', 'fg' => '#0F2942', 'dot' => '#1B3A6B'],
        };
    @endphp

    {{-- Stored choice key: announcement-dismissed.<id> — flipping ANNOUNCEMENT_ID
         in env makes the banner reappear for users who'd previously dismissed it. --}}
    <div x-data="{
            visible: localStorage.getItem('announcement-dismissed.{{ $id }}') !== 'yes',
            dismiss() {
                localStorage.setItem('announcement-dismissed.{{ $id }}', 'yes');
                this.visible = false;
            }
         }"
         x-show="visible"
         x-cloak
         style="position: relative; z-index: 30;">
        <div style="background: {{ $colors['bg'] }}; border-bottom: 1px solid {{ $colors['border'] }}; color: {{ $colors['fg'] }}; padding: 0.6rem 1.25rem; font-size: 13.5px;">
            <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; gap: 1rem;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $colors['dot'] }}; flex-shrink: 0;"></span>
                <span style="flex: 1; line-height: 1.5;">{{ $body }}</span>
                @if ($link && $linkLabel)
                    <a href="{{ $link }}" style="color: inherit; text-decoration: underline; flex-shrink: 0; font-weight: 600;">{{ $linkLabel }}</a>
                @endif
                <button type="button"
                    @click="dismiss"
                    aria-label="{{ __('Dismiss announcement') }}"
                    style="background: transparent; border: 0; color: inherit; padding: 0.25rem; cursor: pointer; opacity: 0.6; flex-shrink: 0;"
                    onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.6">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>
@endif
