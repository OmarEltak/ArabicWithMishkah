<?php

use App\Models\LegalClipping;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Clippings')] class extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $noteDraft = '';

    public string $tagsDraft = '';

    public string $filter = '';

    #[Computed]
    public function clippings()
    {
        $q = LegalClipping::query()
            ->with(['document'])
            ->where('user_id', Auth::id())
            ->latest('id');

        if (trim($this->filter) !== '') {
            $needle = trim($this->filter);
            $q->where(function ($qq) use ($needle) {
                $qq->where('note', 'like', '%'.$needle.'%')
                    ->orWhere('title', 'like', '%'.$needle.'%')
                    ->orWhere('snippet', 'like', '%'.$needle.'%');
            });
        }

        return $q->paginate(15);
    }

    public function startEdit(int $id): void
    {
        $c = LegalClipping::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
        if (! $c) {
            return;
        }
        $this->editingId = $c->id;
        $this->noteDraft = (string) ($c->note ?? '');
        $this->tagsDraft = is_array($c->tags) ? implode(', ', $c->tags) : '';
    }

    public function saveEdit(): void
    {
        $c = LegalClipping::query()
            ->where('id', $this->editingId)
            ->where('user_id', Auth::id())
            ->first();
        if (! $c) {
            return;
        }
        $tags = collect(explode(',', $this->tagsDraft))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();
        $c->update([
            'note' => trim($this->noteDraft) === '' ? null : $this->noteDraft,
            'tags' => $tags ?: null,
        ]);
        $this->editingId = null;
        $this->noteDraft = '';
        $this->tagsDraft = '';
        unset($this->clippings);
        Flux::toast(variant: 'success', text: __('Clipping saved.'));
    }

    public function delete(int $id): void
    {
        $c = LegalClipping::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
        if (! $c) {
            return;
        }
        $c->delete();
        unset($this->clippings);
        Flux::toast(variant: 'success', text: __('Clipping removed.'));
    }
}; ?>

<div class="mx-auto w-full max-w-5xl px-6 py-8">

    {{-- Header --}}
    <div class="border-b hairline pb-6">
        <span class="eyebrow-tag">{{ __('Research') }}</span>
        <h1 class="display mt-2 text-3xl text-zinc-900 dark:text-zinc-50">{{ __('Clippings') }}</h1>
        <p class="mt-1 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Bookmarked excerpts from the legal corpus, tagged and annotated for your own research. Saved from the law-show and search pages.') }}
        </p>
    </div>

    {{-- Filter --}}
    <div class="mt-6 max-w-md">
        <flux:input wire:model.live.debounce.300ms="filter" icon="magnifying-glass" placeholder="{{ __('Filter by note, title, or snippet…') }}" />
    </div>

    {{-- List --}}
    <div class="mt-6 space-y-3">
        @forelse ($this->clippings as $c)
            <div class="card p-5" wire:key="clip-{{ $c->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('lawyer.law-show', ['docId' => $c->legal_document_id]) }}"
                           wire:navigate
                           class="block font-serif text-base font-semibold text-zinc-900 hover:underline dark:text-zinc-100">
                            {{ $c->title ?? $c->document?->title ?? __('Untitled') }}
                        </a>
                        @if ($c->document?->jurisdiction)
                            <span class="mt-1 inline-block text-[10px] uppercase tracking-wider text-zinc-500">
                                {{ $c->document->jurisdiction }}
                                @if ($c->document->category) · {{ $c->document->category }} @endif
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1">
                        <flux:button size="xs" variant="ghost" wire:click="startEdit({{ $c->id }})" icon="pencil-square" title="{{ __('Edit') }}"></flux:button>
                        <flux:button size="xs" variant="danger"
                            wire:click="delete({{ $c->id }})"
                            wire:confirm="{{ __('Remove this clipping?') }}"
                            icon="trash"
                            title="{{ __('Remove') }}"></flux:button>
                    </div>
                </div>

                @if ($c->snippet)
                    <blockquote class="mt-3 border-s-2 border-zinc-300 ps-3 font-serif text-sm leading-relaxed text-zinc-700 dark:border-zinc-700 dark:text-zinc-300">
                        {{ \Illuminate\Support\Str::limit($c->snippet, 320) }}
                    </blockquote>
                @endif

                @if ($editingId === $c->id)
                    <div class="mt-4 space-y-3 rounded-md border hairline p-3">
                        <flux:textarea wire:model="noteDraft" :label="__('Note')" rows="3" placeholder="{{ __('Why is this relevant? What did you take away?') }}" />
                        <flux:input wire:model="tagsDraft" :label="__('Tags')" placeholder="{{ __('comma, separated, tags') }}" />
                        <div class="flex gap-2">
                            <flux:button size="xs" variant="primary" wire:click="saveEdit">{{ __('Save') }}</flux:button>
                            <flux:button size="xs" variant="ghost" wire:click="$set('editingId', null)">{{ __('Cancel') }}</flux:button>
                        </div>
                    </div>
                @else
                    @if ($c->note)
                        <p class="mt-3 text-sm text-zinc-700 dark:text-zinc-300">{{ $c->note }}</p>
                    @endif
                    @if (is_array($c->tags) && count($c->tags) > 0)
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($c->tags as $tag)
                                <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-[11px] text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">#{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                @endif

                <p class="mt-3 text-[11px] text-zinc-400">{{ __('Saved :when', ['when' => $c->created_at?->diffForHumans()]) }}</p>
            </div>
        @empty
            <div class="rounded-md border border-dashed hairline p-12 text-center">
                <flux:icon.bookmark class="mx-auto size-10 text-zinc-300 dark:text-zinc-700" />
                <h2 class="display mt-4 text-lg text-zinc-900 dark:text-zinc-50">{{ __('No clippings yet') }}</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-zinc-500">
                    {{ __('Search the legal corpus and click "Save to clippings" on any article you want to come back to. Add notes and tags to build your personal research file.') }}
                </p>
                <div class="mt-5">
                    <flux:button :href="route('lawyer.law-search')" wire:navigate variant="primary" icon="magnifying-glass">{{ __('Search the corpus') }}</flux:button>
                </div>
            </div>
        @endforelse
    </div>

    @if ($this->clippings->hasPages())
        <div class="mt-6">{{ $this->clippings->onEachSide(1)->links() }}</div>
    @endif
</div>
