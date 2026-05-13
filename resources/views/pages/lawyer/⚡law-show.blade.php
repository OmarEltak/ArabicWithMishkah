<?php

use App\Jobs\IngestEastlawsDocumentJob;
use App\Models\LegalClipping;
use App\Models\LegalDocument;
use App\Services\Ingestion\LegalContentFormatter;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Law')] class extends Component {

    public int $docId = 0;

    public bool $busy = false;

    public function mount(int $docId): void
    {
        $this->docId = $docId;
    }

    #[Computed]
    public function document(): ?LegalDocument
    {
        return LegalDocument::query()->find($this->docId);
    }

    /**
     * @return array<int, array{heading: ?string, body: string, is_article: bool}>
     */
    #[Computed]
    public function sections(): array
    {
        $doc = $this->document;
        if (! $doc || ! $doc->isIngestComplete() || mb_strlen($doc->content) === 0) {
            return [];
        }

        return app(LegalContentFormatter::class)->format($doc->content);
    }

    public function isPending(): bool
    {
        $doc = $this->document;

        return $doc !== null && ! $doc->isIngestComplete() && $doc->ingest_status !== LegalDocument::INGEST_FAILED;
    }

    /**
     * For a stub that was never queued (e.g. user followed a deep link before
     * the search-time dispatch happened), kick off the body fetch now.
     */
    public function fetchNow(): void
    {
        $doc = $this->document;
        if ($doc === null || $doc->isIngestComplete()) {
            return;
        }

        $meta = is_array($doc->metadata) ? $doc->metadata : [];
        $recId = (int) ($meta['eastlaws_id'] ?? 0);
        $recType = (int) ($meta['eastlaws_rec_type'] ?? 0);
        $slug = (string) ($meta['slug'] ?? '');
        $countryId = (int) ($meta['eastlaws_country_id'] ?? 1);

        if ($recId === 0 || $recType === 0) {
            Flux::toast(variant: 'danger', text: __('This document has no upstream reference and cannot be fetched.'));

            return;
        }

        $doc->forceFill(['ingest_status' => LegalDocument::INGEST_QUEUED])->save();
        IngestEastlawsDocumentJob::dispatch(
            recId: $recId,
            recType: $recType,
            slug: $slug,
            countryId: $countryId,
            category: $doc->category,
        );

        Flux::toast(variant: 'success', text: __('Fetch queued. The text will appear once indexing completes.'));
    }

    /**
     * Re-queue a previously-failed fetch.
     */
    public function retry(): void
    {
        $doc = $this->document;
        if ($doc === null || $doc->ingest_status !== LegalDocument::INGEST_FAILED) {
            return;
        }
        $this->fetchNow();
    }

    /**
     * Save the document (optionally a specific section) to the user's
     * clippings. Idempotent on (user, doc, snippet) within ~5 min so a
     * double-click doesn't create a duplicate.
     */
    public function clip(?string $heading = null, ?string $body = null): void
    {
        $doc = $this->document;
        if ($doc === null) {
            return;
        }

        $snippet = trim((string) ($heading ? $heading."\n\n".(string) $body : ($body ?? '')));
        if ($snippet === '') {
            $snippet = Str::limit((string) $doc->content, 280);
        }

        $recent = LegalClipping::query()
            ->where('user_id', Auth::id())
            ->where('legal_document_id', $doc->id)
            ->where('snippet', $snippet)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();
        if ($recent) {
            Flux::toast(variant: 'info', text: __('Already saved to clippings.'));

            return;
        }

        LegalClipping::create([
            'user_id' => Auth::id(),
            'legal_document_id' => $doc->id,
            'title' => $heading ?: $doc->title,
            'snippet' => $snippet,
        ]);

        Flux::toast(variant: 'success', text: __('Saved to clippings.'));
    }
}; ?>

<div class="mx-auto w-full max-w-4xl flex-col gap-6 px-6 py-8"
     @if ($this->isPending()) wire:poll.2s @endif>

    @if (! $this->document)
        <div class="rounded-md border border-dashed hairline p-12 text-center">
            <flux:icon.exclamation-triangle class="mx-auto size-10 text-zinc-300 dark:text-zinc-700" />
            <p class="mt-3 text-sm text-zinc-500">{{ __('Document not found.') }}</p>
            <div class="mt-4">
                <a href="{{ route('lawyer.law-search') }}" class="text-sm text-[var(--color-accent)] hover:underline">{{ __('Back to search') }}</a>
            </div>
        </div>
    @else
        @php($doc = $this->document)

        {{-- Breadcrumb / back --}}
        <div>
            <a href="{{ route('lawyer.law-search') }}" wire:navigate class="text-xs text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300">
                ← {{ __('Back to search') }}
            </a>
        </div>

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4 border-b hairline pb-6">
            <div class="min-w-0 flex-1">
                <span class="eyebrow-tag">{{ $doc->source_label }}</span>
                <h1 class="display mt-3 text-2xl text-zinc-900 dark:text-zinc-50">{{ $doc->title }}</h1>

                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                    @if ($doc->jurisdiction)
                        <span class="rounded-md border hairline px-2 py-0.5">{{ $doc->jurisdiction }}</span>
                    @endif
                    @if ($doc->category)
                        <span class="rounded-md bg-[var(--color-parchment)] px-2 py-0.5 font-medium uppercase tracking-wide text-[var(--color-accent-content)] dark:bg-zinc-800 dark:text-zinc-300">{{ $doc->category }}</span>
                    @endif
                    <span class="font-mono tabular-nums">v{{ $doc->version ?? 1 }}</span>
                    @if ($doc->isOfficialSource() && $doc->last_verified_at)
                        <span>·</span>
                        <span>{{ __('Verified :when', ['when' => $doc->last_verified_at->diffForHumans()]) }}</span>
                    @endif
                    @if ($doc->isStale())
                        <span class="pill pill-warning">{{ __('stale') }}</span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2"
                 x-data="{
                     async copy(text, btn) {
                         try { await navigator.clipboard.writeText(text); }
                         catch (e) { return; }
                         const orig = btn.innerText;
                         btn.innerText = '{{ addslashes(__('Copied')) }}';
                         setTimeout(() => { btn.innerText = orig; }, 1500);
                     }
                 }">
                @php($_meta = is_array($doc->metadata) ? $doc->metadata : [])
                @php($_fullCitation = trim(($_meta['citation_en'] ?? $_meta['citation_ar'] ?? '') . (($_meta['citation_en'] ?? null) ? ' (' . $doc->title . ')' : $doc->title)))
                <button type="button"
                    @click="copy(@js($_fullCitation), $event.currentTarget)"
                    class="inline-flex items-center rounded-md border hairline px-3 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                    <flux:icon.clipboard class="me-1.5 size-3.5" />
                    {{ __('Copy citation') }}
                </button>
                <flux:button size="xs" variant="ghost"
                    wire:click="clip"
                    wire:loading.attr="disabled"
                    wire:target="clip"
                    icon="bookmark">
                    {{ __('Save to clippings') }}
                </flux:button>
                @if (($doc->version ?? 1) > 1)
                    <a href="{{ route('lawyer.document-diff', ['docId' => $doc->id]) }}" wire:navigate
                       class="inline-flex items-center rounded-md border hairline px-3 py-1.5 text-xs text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        {{ __('View changes') }}
                    </a>
                @endif
            </div>
        </div>

        {{-- Status banner for non-complete states --}}
        @switch($doc->ingest_status ?? 'complete')
            @case('stub')
                <div class="rounded-lg border hairline bg-[var(--color-warning-soft)]/50 p-4 dark:bg-zinc-900">
                    <div class="flex items-start gap-3">
                        <flux:icon.clock class="mt-0.5 size-5 flex-shrink-0 text-[var(--color-warning)]" />
                        <div class="flex-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('Full text not loaded yet') }}</p>
                            <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                {{ __('This law was discovered in a recent search but its body has not been fetched. Press the button below to queue indexing.') }}
                            </p>
                            <div class="mt-3">
                                <flux:button size="sm" variant="primary" wire:click="fetchNow" :disabled="$busy" icon="arrow-down-tray">
                                    {{ __('Fetch full text') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </div>
                @break

            @case('queued')
                <div class="rounded-lg border hairline bg-[var(--color-info-soft)]/50 p-4 dark:bg-zinc-900">
                    <div class="flex items-start gap-3">
                        <flux:icon.clock class="mt-0.5 size-5 flex-shrink-0 text-[var(--color-info)]" />
                        <div class="flex-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('Queued for indexing') }}</p>
                            <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                {{ __('Waiting for a worker to pick up the fetch. Upstream is rate-limited to roughly one request every three seconds.') }}
                            </p>
                        </div>
                    </div>
                </div>
                @break

            @case('indexing')
                <div class="rounded-lg border hairline bg-[var(--color-info-soft)]/50 p-4 dark:bg-zinc-900">
                    <div class="flex items-start gap-3">
                        <flux:icon.arrow-path class="mt-0.5 size-5 flex-shrink-0 animate-spin text-[var(--color-info)]" />
                        <div class="flex-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('Indexing in progress…') }}</p>
                            <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                {{ __('Fetching the body, chunking, and embedding. The text will appear here shortly.') }}
                            </p>
                        </div>
                    </div>
                </div>
                @break

            @case('failed')
                <div class="rounded-lg border hairline bg-[var(--color-danger-soft)]/50 p-4 dark:bg-zinc-900">
                    <div class="flex items-start gap-3">
                        <flux:icon.exclamation-triangle class="mt-0.5 size-5 flex-shrink-0 text-[var(--color-danger)]" />
                        <div class="flex-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('Indexing failed') }}</p>
                            <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                                {{ __('The last fetch did not complete. You can retry it now.') }}
                            </p>
                            <div class="mt-3">
                                <flux:button size="sm" variant="primary" wire:click="retry" :disabled="$busy" icon="arrow-path">
                                    {{ __('Retry') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </div>
                @break
        @endswitch

        {{-- Body --}}
        @if ($doc->isIngestComplete() && mb_strlen($doc->content) > 0)
            @php($_sections = $this->sections)
            @php($_citationMeta = is_array($doc->metadata) ? $doc->metadata : [])
            @php($_docCitation = $_citationMeta['citation_en'] ?? $_citationMeta['citation_ar'] ?? $doc->title)
            <article class="space-y-3" dir="rtl"
                     x-data="{
                         async copy(text, btn) {
                             try { await navigator.clipboard.writeText(text); }
                             catch (e) { return; }
                             const orig = btn.innerText;
                             btn.innerText = '{{ addslashes(__('Copied')) }}';
                             setTimeout(() => { btn.innerText = orig; }, 1500);
                         }
                     }">
                @foreach ($_sections as $section)
                    @if ($section['is_article'])
                        @php($_citation = trim(($section['heading'] ?? '') . ' — ' . $_docCitation))
                        {{-- Article: rendered as a discrete card with the heading
                             on its own visually distinct row. --}}
                        <section class="card p-5 group">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-serif text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $section['heading'] }}
                                </h3>
                                <div class="flex flex-shrink-0 items-center gap-1 opacity-0 transition group-hover:opacity-100 focus-within:opacity-100">
                                    <button type="button"
                                        @click="copy(@js($_citation), $event.currentTarget)"
                                        class="rounded-md border hairline px-2 py-1 text-[11px] font-medium text-zinc-500 transition hover:bg-zinc-50 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                                        title="{{ __('Copy citation') }}"
                                        dir="ltr">
                                        {{ __('Copy citation') }}
                                    </button>
                                    <button type="button"
                                        wire:click="clip(@js($section['heading']), @js($section['body']))"
                                        class="rounded-md border hairline px-2 py-1 text-[11px] font-medium text-zinc-500 transition hover:bg-zinc-50 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                                        title="{{ __('Save to clippings') }}"
                                        dir="ltr">
                                        {{ __('Clip') }}
                                    </button>
                                </div>
                            </div>
                            @if ($section['body'] !== '')
                                <div class="mt-3 whitespace-pre-line font-serif text-[15px] leading-loose text-zinc-800 dark:text-zinc-200">{{ $section['body'] }}</div>
                            @endif
                        </section>
                    @elseif ($section['heading'] !== null)
                        {{-- Structural heading (فصل / باب / etc.) — top-tier separator. --}}
                        <section class="mt-6 first:mt-0">
                            <h2 class="border-b hairline pb-2 font-serif text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $section['heading'] }}
                            </h2>
                            @if ($section['body'] !== '')
                                <div class="mt-3 whitespace-pre-line font-serif text-[15px] leading-loose text-zinc-700 dark:text-zinc-300">{{ $section['body'] }}</div>
                            @endif
                        </section>
                    @elseif ($section['body'] !== '')
                        {{-- Unheaded prose (preamble / closing block). --}}
                        <section class="card p-5">
                            <div class="whitespace-pre-line font-serif text-[15px] leading-loose text-zinc-800 dark:text-zinc-200">{{ $section['body'] }}</div>
                        </section>
                    @endif
                @endforeach
            </article>

            @if ($doc->chunk_count > 0)
                <p class="mt-4 text-xs text-zinc-500">
                    {{ __(':n sections · :chars characters', [
                        'n' => count($_sections),
                        'chars' => number_format(mb_strlen($doc->content)),
                    ]) }}
                </p>
            @endif
        @endif
    @endif
</div>
