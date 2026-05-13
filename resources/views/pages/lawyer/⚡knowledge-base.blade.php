<?php

use App\Models\LegalDocument;
use App\Services\Ingestion\FreshnessService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Knowledge Base')] class extends Component {

    public bool $busy = false;

    // Filter chips — persisted in URL so a corporate-focused lawyer can
    // bookmark a pre-filtered library view (e.g. ?j=EG&cat=companies).
    #[Url(as: 'j')]
    public string $filterJurisdiction = '';
    #[Url(as: 'cat')]
    public string $filterCategory = '';

    #[Computed]
    public function documents()
    {
        $q = LegalDocument::query()
            ->where(function ($qq) {
                $qq->where('user_id', Auth::id())->orWhereNull('user_id');
            });
        if ($this->filterJurisdiction !== '') {
            $q->where('jurisdiction', $this->filterJurisdiction);
        }
        if ($this->filterCategory !== '') {
            $q->where('category', $this->filterCategory);
        }

        return $q->latest('id')->limit(100)->get();
    }

    /** Available filter values, derived from what's actually in the KB. */
    #[Computed]
    public function jurisdictionFacets(): array
    {
        return LegalDocument::query()
            ->whereNotNull('jurisdiction')
            ->selectRaw('jurisdiction, COUNT(*) as c')
            ->groupBy('jurisdiction')
            ->orderBy('jurisdiction')
            ->pluck('c', 'jurisdiction')
            ->all();
    }

    #[Computed]
    public function categoryFacets(): array
    {
        $q = LegalDocument::query()->whereNotNull('category');
        if ($this->filterJurisdiction !== '') {
            $q->where('jurisdiction', $this->filterJurisdiction);
        }

        return $q->selectRaw('category, COUNT(*) as c')
            ->groupBy('category')
            ->orderBy('category')
            ->pluck('c', 'category')
            ->all();
    }

    /**
     * Force an immediate freshness check for one official-source document.
     * Synchronous so the user sees the outcome (changed/unchanged) right
     * away — for bulk refreshes the scheduler queues VerifyDocumentFreshnessJob.
     */
    public function refresh(int $id, FreshnessService $freshness): void
    {
        $doc = LegalDocument::query()->find($id);
        if (! $doc) {
            return;
        }
        if (! $doc->isOfficialSource()) {
            Flux::toast(variant: 'warning', text: __('Only documents from an official legal database can be re-verified automatically.'));

            return;
        }
        if (! $freshness->isEnabled()) {
            Flux::toast(variant: 'danger', text: __('Legal database integration is disabled. Configure credentials in .env.'));

            return;
        }

        $this->busy = true;
        try {
            $outcome = $freshness->verify($doc);
            match ($outcome) {
                'unchanged' => Flux::toast(variant: 'success', text: __('Verified — no changes detected.')),
                'updated'   => Flux::toast(variant: 'success', text: __('Updated — new version ingested.')),
                'failed'    => Flux::toast(variant: 'danger',  text: __('Verification failed. Check logs.')),
                default     => Flux::toast(variant: 'warning', text: __('Skipped.')),
            };
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        } finally {
            $this->busy = false;
        }
    }
}; ?>

<div class="mx-auto w-full max-w-7xl flex-col gap-8 px-6 py-8">

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4 border-b hairline pb-8">
        <div class="min-w-0">
            <span class="eyebrow-tag">{{ __('Knowledge base') }}</span>
            <h1 class="display mt-3 text-3xl text-zinc-900 dark:text-zinc-50">{{ __('Legal authority library') }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                {{ __('Browse the legal corpus used as grounding context when the assistant drafts. Documents are auto-checked for upstream changes on a schedule. To add new laws, use Search Laws in the sidebar.') }}
            </p>
        </div>
    </div>

    {{-- Documents list --}}
    <div class="mt-10">
        <div class="mb-4 flex items-baseline justify-between">
            <span class="eyebrow-tag">{{ __('Library') }}</span>
            <p class="text-xs text-zinc-500 tabular-nums">{{ count($this->documents) }} {{ __('documents') }}</p>
        </div>

        {{-- Jurisdiction × category filter chips --}}
        @if (count($this->jurisdictionFacets) > 1 || count($this->categoryFacets) > 0)
            <div class="mb-4 space-y-3 rounded-lg border hairline bg-white p-3 dark:bg-zinc-900">
                @if (count($this->jurisdictionFacets) > 1)
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="eyebrow !text-[10px] me-1">{{ __('Jurisdiction') }}</span>
                        <button type="button" wire:click="$set('filterJurisdiction', '')"
                            class="rounded-full px-2.5 py-0.5 text-[11px] transition
                            {{ $filterJurisdiction === '' ? 'bg-[var(--color-ink)] text-white dark:bg-zinc-100 dark:text-zinc-900' : 'border hairline text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                            {{ __('All') }}
                        </button>
                        @foreach ($this->jurisdictionFacets as $code => $count)
                            <button type="button" wire:click="$set('filterJurisdiction', '{{ $code }}')"
                                class="rounded-full px-2.5 py-0.5 text-[11px] transition
                                {{ $filterJurisdiction === $code ? 'bg-[var(--color-ink)] text-white dark:bg-zinc-100 dark:text-zinc-900' : 'border hairline text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                                {{ $code }} <span class="opacity-60">{{ $count }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                @if (count($this->categoryFacets) > 0)
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="eyebrow !text-[10px] me-1">{{ __('Category') }}</span>
                        <button type="button" wire:click="$set('filterCategory', '')"
                            class="rounded-full px-2.5 py-0.5 text-[11px] transition
                            {{ $filterCategory === '' ? 'bg-[var(--color-accent)] text-white' : 'border hairline text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                            {{ __('All') }}
                        </button>
                        @foreach ($this->categoryFacets as $cat => $count)
                            <button type="button" wire:click="$set('filterCategory', '{{ $cat }}')"
                                class="rounded-full px-2.5 py-0.5 text-[11px] transition
                                {{ $filterCategory === $cat ? 'bg-[var(--color-accent)] text-white' : 'border hairline text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800' }}">
                                {{ $cat }} <span class="opacity-60">{{ $count }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
        @if ($this->documents->isEmpty())
            <div class="rounded-md border border-dashed hairline p-12 text-center">
                <flux:icon.book-open class="mx-auto size-10 text-zinc-300 dark:text-zinc-700" />
                <p class="mt-3 text-sm text-zinc-500">{{ __('No documents yet. Use Search Laws in the sidebar to grow the corpus.') }}</p>
            </div>
        @else
            <div class="overflow-hidden rounded-lg border hairline bg-white dark:bg-zinc-900">
                <table class="w-full text-sm">
                    <thead class="border-b hairline bg-zinc-50/50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-950/30">
                        <tr class="text-start">
                            <th class="px-4 py-3 text-start font-medium">{{ __('Title') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Source') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Category') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Lang') }}</th>
                            <th class="px-4 py-3 text-end font-medium">{{ __('Chunks') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Version') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Verified') }}</th>
                            <th class="px-4 py-3 text-start font-medium">{{ __('Policy') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->documents as $doc)
                            @php(
                                $sourcePill = $doc->isOfficialSource() ? 'pill pill-info' : 'pill pill-neutral'
                            )
                            <tr class="border-t hairline transition hover:bg-[var(--color-paper-edge)]/40 dark:hover:bg-zinc-950/30">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-zinc-900 dark:text-zinc-100 line-clamp-1">{{ $doc->title }}</p>
                                    @if ($doc->jurisdiction)
                                        <p class="mt-0.5 text-[11px] text-zinc-500">{{ $doc->jurisdiction }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3"><span class="{{ $sourcePill }}" title="{{ $doc->source_label }}">{{ $doc->source_short }}</span></td>
                                <td class="px-4 py-3">
                                    @if ($doc->category)
                                        <span class="rounded-md bg-[var(--color-parchment)] px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-[var(--color-accent-content)] dark:bg-zinc-800 dark:text-zinc-300">{{ $doc->category }}</span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $doc->language }}</td>
                                <td class="px-4 py-3 text-end font-mono text-zinc-700 dark:text-zinc-300 tabular-nums">{{ $doc->chunk_count }}</td>
                                <td class="px-4 py-3"><span class="font-mono text-xs text-zinc-600 dark:text-zinc-400 tabular-nums">v{{ $doc->version ?? 1 }}</span></td>
                                <td class="px-4 py-3">
                                    @if (! $doc->isOfficialSource())
                                        <span class="text-zinc-400">—</span>
                                    @elseif ($doc->last_verified_at)
                                        @if ($doc->isStale())
                                            <span class="pill pill-warning">{{ __('stale') }}</span>
                                        @else
                                            <span class="text-xs text-zinc-600 dark:text-zinc-400" title="{{ $doc->last_verified_at }}">
                                                {{ $doc->last_verified_at->diffForHumans() }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="pill pill-warning">{{ __('never') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($doc->isOfficialSource())
                                        <span class="font-mono text-[11px] text-zinc-500">{{ $doc->refresh_policy ?? 'standard' }}</span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end whitespace-nowrap">
                                    @if (($doc->version ?? 1) > 1)
                                        <a href="{{ route('lawyer.document-diff', ['docId' => $doc->id]) }}"
                                           class="mx-1 inline-flex items-center rounded-md border hairline px-2 py-1 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">{{ __('Diff') }}</a>
                                    @endif
                                    @if ($doc->isOfficialSource())
                                        <button wire:click="refresh({{ $doc->id }})" :disabled="$busy"
                                           class="mx-1 inline-flex items-center rounded-md border hairline px-2 py-1 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">{{ __('Refresh') }}</button>
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
