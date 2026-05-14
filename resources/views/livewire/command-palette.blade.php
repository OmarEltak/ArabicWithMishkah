<div x-data="{
        get isOpen() { return @js($open); },
        active: 0,
        onKeydown(e) {
            const palette = document.querySelector('[data-cmdk-root]');
            if (!palette) return;
            const items = palette.querySelectorAll('[data-cmdk-item]');
            if (e.key === 'Escape') { $wire.close(); e.preventDefault(); return; }
            if (e.key === 'ArrowDown') { this.active = Math.min(this.active + 1, items.length - 1); items[this.active]?.scrollIntoView({block:'nearest'}); e.preventDefault(); return; }
            if (e.key === 'ArrowUp') { this.active = Math.max(this.active - 1, 0); items[this.active]?.scrollIntoView({block:'nearest'}); e.preventDefault(); return; }
            if (e.key === 'Enter') { items[this.active]?.click(); e.preventDefault(); return; }
        },
    }"
    @keydown.window="if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); $wire.show(); }"
    @keydown.window="if (isOpen) onKeydown(event)"
    @open-command-palette.window="$wire.show()"
    x-cloak>

    <template x-if="isOpen">
        <div class="fixed inset-0 z-[100] flex items-start justify-center bg-black/40 px-4 pt-24"
             data-cmdk-root
             @click.self="$wire.close()">

            <div class="w-full max-w-xl overflow-hidden rounded-xl border hairline bg-white shadow-2xl dark:bg-zinc-900">
                <div class="flex items-center gap-3 border-b hairline px-4">
                    <span class="font-mono text-xs text-zinc-400">⌘K</span>
                    <input type="text"
                        wire:model.live.debounce.200ms="q"
                        @input="active = 0"
                        x-init="$nextTick(() => $el.focus())"
                        placeholder="{{ __('Search contracts, matters, templates, laws…') }}"
                        class="flex-1 border-0 bg-transparent py-4 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:ring-0 dark:text-zinc-100"
                        autocomplete="off" />
                    <button type="button" wire:click="close"
                        class="rounded-md p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                        title="{{ __('Close') }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <ul class="max-h-[60vh] overflow-y-auto py-1">
                    @forelse ($this->results as $i => $r)
                        <li>
                            <a href="{{ $r['url'] }}"
                                wire:navigate
                                wire:click="close"
                                data-cmdk-item
                                :class="active === {{ $i }} ? 'bg-[var(--color-parchment)] dark:bg-zinc-800' : ''"
                                @mouseenter="active = {{ $i }}"
                                class="flex items-center justify-between gap-3 px-4 py-2.5 transition hover:bg-[var(--color-parchment)] dark:hover:bg-zinc-800">
                                <span class="truncate text-sm text-zinc-900 dark:text-zinc-100">{{ $r['label'] }}</span>
                                @if ($r['sub'])
                                    <span class="flex-shrink-0 text-[11px] text-zinc-500">{{ $r['sub'] }}</span>
                                @endif
                            </a>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-center text-sm text-zinc-500">{{ __('No results.') }}</li>
                    @endforelse
                </ul>

                <div class="flex items-center justify-between border-t hairline bg-zinc-50/60 px-4 py-2 text-[11px] text-zinc-500 dark:bg-zinc-950/40">
                    <span class="flex items-center gap-3">
                        <span><kbd class="rounded border hairline px-1 py-0.5 font-mono text-[10px]">↑</kbd> <kbd class="rounded border hairline px-1 py-0.5 font-mono text-[10px]">↓</kbd> {{ __('Navigate') }}</span>
                        <span><kbd class="rounded border hairline px-1 py-0.5 font-mono text-[10px]">↵</kbd> {{ __('Open') }}</span>
                        <span><kbd class="rounded border hairline px-1 py-0.5 font-mono text-[10px]">Esc</kbd> {{ __('Close') }}</span>
                    </span>
                </div>
            </div>
        </div>
    </template>
</div>
