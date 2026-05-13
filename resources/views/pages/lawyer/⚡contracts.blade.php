<?php

use App\Models\Contract;
use App\Models\LegalChunk;
use App\Services\Audit\AuditLogger;
use App\Services\Contracts\BilingualTranslator;
use App\Services\Contracts\ContractDiffer;
use App\Services\Contracts\ContractExporter;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Contracts')] class extends Component
{
    use WithPagination;

    public ?int $openId = null;

    public string $editBody = '';

    public string $editTitle = '';

    public string $viewLang = 'ar'; // 'ar' = source only, 'en'/'fr' = bilingual side-by-side

    public bool $translating = false;

    public bool $showHistory = false;

    public ?int $compareIdx = null; // index in body_history (0 = most recent prior version)

    /**
     * Initialize from URL query string. /lawyer/contracts?contract=5 will
     * open contract 5 on first paint, before Livewire JS has hydrated. This
     * removes the wire:click hydration race that was making the first click
     * after a fresh navigation feel unresponsive.
     */
    public function mount(): void
    {
        $cid = (int) request()->query('contract', 0);
        if ($cid > 0) {
            $c = Contract::query()->where('id', $cid)->where('user_id', Auth::id())->first();
            if ($c) {
                $this->openId = $cid;
                $this->editTitle = $c->title;
                $this->editBody = $c->body;
            }
        }
    }

    #[Computed]
    public function contracts()
    {
        return Contract::query()
            ->where('user_id', Auth::id())
            ->latest('id')
            ->paginate(20);
    }

    #[Computed]
    public function active(): ?Contract
    {
        if (! $this->openId) {
            return null;
        }

        return Contract::query()
            ->where('id', $this->openId)
            ->where('user_id', Auth::id())
            ->first();
    }

    public function open(int $id): void
    {
        $c = Contract::query()->where('id', $id)->where('user_id', Auth::id())->first();
        if (! $c) {
            return;
        }
        $this->openId = $id;
        $this->editTitle = $c->title;
        $this->editBody = $c->body;
    }

    public function close(): void
    {
        $this->openId = null;
        $this->editTitle = '';
        $this->editBody = '';
    }

    public function save(): void
    {
        $this->validate([
            'editTitle' => 'required|string|min:1|max:255',
            'editBody' => 'required|string|min:1',
        ]);
        $c = $this->active;
        if (! $c) {
            return;
        }
        $oldTitle = $c->title;
        $oldBody = $c->body;
        $bodyChanged = $oldBody !== $this->editBody;

        // Snapshot the previous body into body_history (capped at 10 entries)
        // before we overwrite. This is what makes the version-diff feature work.
        $history = is_array($c->body_history) ? $c->body_history : [];
        if ($bodyChanged) {
            array_unshift($history, [
                'version' => $c->version,
                'body' => $oldBody,
                'saved_at' => now()->toIso8601String(),
                'saved_by' => Auth::user()->name,
            ]);
            $history = array_slice($history, 0, 10);
        }

        $c->update([
            'title' => $this->editTitle,
            'body' => $this->editBody,
            'version' => $bodyChanged ? $c->version + 1 : $c->version,
            'body_history' => $history,
        ]);
        app(AuditLogger::class)->log(
            action: 'contract.updated',
            subject: $c,
            summary: 'Manual edit',
            metadata: [
                'title_changed' => $oldTitle !== $this->editTitle,
                'body_length_before' => mb_strlen($oldBody),
                'body_length_after' => mb_strlen($this->editBody),
                'new_version' => $c->version,
            ],
        );
        Flux::toast(variant: 'success', text: __('Contract saved.'));
        unset($this->contracts, $this->active);
    }

    public function toggleHistory(): void
    {
        $this->showHistory = ! $this->showHistory;
        if (! $this->showHistory) {
            $this->compareIdx = null;
        }
    }

    public function selectVersion(int $idx): void
    {
        $this->compareIdx = $idx;
    }

    /**
     * Return diff rows comparing the contract's CURRENT body (what's edited)
     * with the body_history entry at $compareIdx. Returns null when no diff
     * is being shown.
     *
     * @return array{added:int, removed:int, unchanged:int, rows: array<int, array{type:string, text:string}>}|null
     */
    #[Computed]
    public function diff(): ?array
    {
        if ($this->compareIdx === null) {
            return null;
        }
        $c = $this->active;
        if (! $c || ! is_array($c->body_history)) {
            return null;
        }
        $entry = $c->body_history[$this->compareIdx] ?? null;
        if (! $entry || empty($entry['body'])) {
            return null;
        }

        return app(ContractDiffer::class)->diff($entry['body'], $c->body);
    }

    public function finalize(): void
    {
        $c = $this->active;
        if (! $c) {
            return;
        }
        $c->update(['status' => 'finalized']);
        app(AuditLogger::class)->log(
            action: 'contract.finalized',
            subject: $c,
            summary: 'Marked finalized',
        );
        Flux::toast(variant: 'success', text: __('Contract finalized.'));
        unset($this->contracts, $this->active);
    }

    public function downloadDocx(ContractExporter $exporter, ?string $bilingualLang = null)
    {
        $c = $this->active;
        if (! $c) {
            return null;
        }
        $bytes = $exporter->toDocx($c, $bilingualLang);
        $name = $exporter->suggestedFilename($c, $bilingualLang);

        return response()->streamDownload(
            fn () => print ($bytes),
            $name,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        );
    }

    public function downloadPdf(\App\Services\Contracts\ContractPdfExporter $exporter, ?string $bilingualLang = null)
    {
        $c = $this->active;
        if (! $c) {
            return null;
        }
        $bytes = $exporter->toPdf($c, $bilingualLang);
        $name = $exporter->suggestedFilename($c, $bilingualLang);

        return response()->streamDownload(
            fn () => print ($bytes),
            $name,
            ['Content-Type' => 'application/pdf']
        );
    }

    #[Computed]
    public function auditRows()
    {
        $c = $this->active;
        if (! $c || ! is_array($c->citation_audit)) {
            return collect();
        }

        return collect($c->citation_audit['markers'] ?? []);
    }

    #[Computed]
    public function auditSummary(): array
    {
        $c = $this->active;
        if (! $c || ! is_array($c->citation_audit)) {
            return ['verified' => 0, 'uncertain' => 0, 'unverified' => 0, 'total' => 0];
        }

        return $c->citation_audit['summary'] ?? ['verified' => 0, 'uncertain' => 0, 'unverified' => 0, 'total' => 0];
    }

    /**
     * The CorpusCitationGate report attached at draft / edit time. Different
     * from auditRows() — auditRows audits `[n]` markers vs chunks the model
     * saw; this audits "Article N of Code Y" citation strings against the
     * actual indexed corpus for the chosen jurisdiction. Both are surfaced
     * so the lawyer can see citations are real AND that the model didn't
     * fabricate the marker→chunk link.
     */
    #[Computed]
    public function corpusGate(): ?array
    {
        $c = $this->active;
        if (! $c || ! is_array($c->corpus_gate)) {
            return null;
        }

        return $c->corpus_gate;
    }

    #[Computed]
    public function citationChunks()
    {
        $c = $this->active;
        if (! $c || ! is_array($c->citation_audit)) {
            return collect();
        }
        $chunkIds = collect($c->citation_audit['markers'] ?? [])
            ->pluck('chunk_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
        if ($chunkIds === []) {
            return collect();
        }

        return LegalChunk::with('document')
            ->whereIn('id', $chunkIds)
            ->get()
            ->keyBy('id');
    }

    /**
     * Generate (or regenerate) a working translation of the open contract
     * into the requested target language. Stores result on Contract->translations.
     * Arabic body remains the legally binding source.
     */
    public function translate(string $lang): void
    {
        $c = $this->active;
        if (! $c) {
            return;
        }
        if (! in_array($lang, ['en', 'fr'], true)) {
            return;
        }

        $this->translating = true;

        try {
            $translator = app(BilingualTranslator::class);
            $jurisdiction = is_array($c->variables) && ! empty($c->variables['jurisdiction'])
                ? (string) $c->variables['jurisdiction']
                : 'EG';

            $result = $translator->translate(
                arabicBody: $c->body,
                targetLanguage: $lang,
                jurisdiction: $jurisdiction,
            );

            $existing = is_array($c->translations) ? $c->translations : [];
            $existing[$lang] = $result;
            $c->update(['translations' => $existing]);

            app(AuditLogger::class)->log(
                action: 'contract.translated',
                subject: $c,
                summary: 'Translated to '.strtoupper($lang),
                metadata: [
                    'language' => $lang,
                    'jurisdiction' => $result['glossary_jurisdiction'],
                    'source_chars' => mb_strlen($c->body),
                    'output_chars' => mb_strlen($result['body']),
                    'warning_count' => count($result['warnings']),
                    'model' => $result['model'],
                ],
            );

            $this->viewLang = $lang;
            unset($this->active, $this->contracts);
            $warnCount = count($result['warnings']);
            Flux::toast(
                variant: $warnCount > 0 ? 'warning' : 'success',
                text: $warnCount > 0
                    ? __('Translated. :n term(s) need lawyer review (marked [?: …]).', ['n' => $warnCount])
                    : __('Translation complete.'),
            );
        } catch (Throwable $e) {
            // Log full detail (including provider class + status) for ops.
            // Show the user a neutral, branded message — never leak the
            // underlying provider/model identity.
            Log::channel('ai')->warning('Bilingual translation failed', [
                'contract_id' => $c->id,
                'lang' => $lang,
                'error' => $e->getMessage(),
            ]);
            Flux::toast(variant: 'danger', text: __('Translation could not be completed right now. Please try again in a minute.'));
        } finally {
            $this->translating = false;
        }
    }

    public function setViewLang(string $lang): void
    {
        $this->viewLang = in_array($lang, ['ar', 'en', 'fr'], true) ? $lang : 'ar';
    }

    public function delete(int $id): void
    {
        $c = Contract::query()->where('id', $id)->where('user_id', Auth::id())->first();
        if ($c) {
            app(AuditLogger::class)->log(
                action: 'contract.deleted',
                subject: $c,
                summary: 'Contract deleted',
                metadata: ['title' => $c->title, 'status' => $c->status],
            );
            $c->delete();
            $this->openId = null;
            unset($this->contracts);
            Flux::toast(variant: 'success', text: __('Deleted.'));
        }
    }
}; ?>

<div class="flex h-[calc(100vh-1rem)] flex-1 flex-col gap-3 p-3 md:gap-4 md:p-4 lg:flex-row">
    {{-- Mobile contract picker — surfaces below lg: where the sidebar is hidden --}}
    <details class="lg:hidden rounded-lg border hairline bg-white dark:bg-zinc-900">
        <summary class="cursor-pointer px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100 select-none">
            {{ $this->active ? \Illuminate\Support\Str::limit($this->active->title, 50) : __('Select a contract') }}
            <span class="ms-1 text-xs text-zinc-500 tabular-nums">({{ count($this->contracts) }})</span>
        </summary>
        <ul class="max-h-[40vh] overflow-y-auto border-t hairline p-2">
            @foreach ($this->contracts as $c)
                <li>
                    <a href="{{ route('lawyer.contracts') }}?contract={{ $c->id }}" wire:navigate
                       class="block rounded-md px-3 py-2 text-sm transition {{ $openId === $c->id ? 'bg-[var(--color-parchment)] dark:bg-zinc-800' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                        <span class="block truncate font-medium text-zinc-900 dark:text-zinc-100">{{ $c->title }}</span>
                        <span class="text-[10px] text-zinc-500 tabular-nums">v{{ $c->version }} · {{ $c->status }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </details>

    {{-- Contracts list (desktop only) --}}
    <aside class="hidden w-80 flex-shrink-0 flex-col overflow-hidden rounded-lg border hairline bg-white dark:bg-zinc-900 lg:flex">
        <div class="border-b hairline px-4 py-3">
            <p class="eyebrow">{{ __('Saved contracts') }}</p>
            <p class="text-xs text-zinc-500">{{ count($this->contracts) }} {{ __('total') }}</p>
        </div>
        <ul class="flex-1 overflow-y-auto p-2">
            @forelse ($this->contracts as $c)
                @php(
                    $statusPill = match($c->status) {
                        'finalized' => 'pill pill-success',
                        'archived' => 'pill pill-neutral',
                        default => 'pill pill-info',
                    }
                )
                <li>
                    {{-- Anchor + wire:click: anchor handles the click before
                         Livewire hydrates (URL updates immediately, page
                         reloads with ?contract=N which mount() honors); the
                         wire:click takes over once hydrated for SPA-like
                         feel. Either path opens the contract. --}}
                    <a href="{{ route('lawyer.contracts') }}?contract={{ $c->id }}"
                        wire:click.prevent="open({{ $c->id }})"
                        wire:navigate
                        class="block w-full rounded-md px-3 py-3 text-start transition
                        {{ $openId === $c->id ? 'bg-[var(--color-parchment)] dark:bg-zinc-800' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                        <span class="block truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $c->title }}</span>
                        <div class="mt-1.5 flex items-center justify-between gap-2">
                            <span class="{{ $statusPill }}">{{ $c->status }}</span>
                            <span class="text-[10px] text-zinc-500 tabular-nums">v{{ $c->version }} · {{ $c->updated_at?->diffForHumans() }}</span>
                        </div>
                    </a>
                </li>
            @empty
                <li class="p-6 text-center">
                    <p class="text-xs text-zinc-500">{{ __('No contracts yet.') }}</p>
                    <a href="{{ route('lawyer.chat') }}" wire:navigate class="mt-1 inline-block text-xs text-[var(--color-accent)] hover:underline">{{ __('Start a draft') }} →</a>
                </li>
            @endforelse
        </ul>

        @if ($this->contracts->hasPages())
            <div class="border-t hairline px-3 py-2">
                {{ $this->contracts->onEachSide(1)->links() }}
            </div>
        @endif
    </aside>

    {{-- Editor pane --}}
    <main class="flex-1 overflow-hidden rounded-lg border hairline bg-white dark:bg-zinc-900">
        @if (! $this->active)
            <div class="flex h-full items-center justify-center p-8 surface-paper">
                <div class="text-center max-w-sm">
                    <div class="mx-auto bezel inline-block">
                        <div class="bezel-inner px-6 py-5">
                            <flux:icon.document-text class="mx-auto size-9 text-zinc-300 dark:text-zinc-700" />
                        </div>
                    </div>
                    <h2 class="display mt-6 text-xl text-zinc-900 dark:text-zinc-100">
                        {{ __('Select a contract to view or edit.') }}
                    </h2>
                    <p class="mt-2 text-[13px] text-zinc-500 dark:text-zinc-400">
                        {{ __('Or') }} <a href="{{ route('lawyer.chat') }}" wire:navigate class="text-[var(--color-accent)] hover:underline">{{ __('start a new draft') }}</a>.
                    </p>
                </div>
            </div>
        @else
            @php($c = $this->active)
            @php($s = $this->auditSummary)
            <div class="flex h-full flex-col">
                {{-- Toolbar — segmented islands group related actions --}}
                @php($translations = is_array($c->translations) ? $c->translations : [])
                @php($hasEn = isset($translations['en']))
                <header class="flex flex-wrap items-center justify-between gap-3 border-b hairline px-4 py-3 md:px-6 md:py-4">
                    <div class="min-w-0 flex-1 basis-full md:basis-auto">
                        <div class="flex items-center gap-2">
                            <span class="eyebrow-tag">v{{ $c->version }}</span>
                            @php($statusPillClass = match($c->status) {
                                'finalized' => 'pill pill-success',
                                'needs_review' => 'pill pill-danger',
                                'archived' => 'pill pill-neutral',
                                default => 'pill pill-info',
                            })
                            <span class="{{ $statusPillClass }}">{{ str_replace('_', ' ', $c->status) }}</span>
                        </div>
                        <flux:input wire:model="editTitle" class="mt-1.5 !font-serif !text-base md:!text-lg" />
                    </div>
                    <div class="flex flex-shrink-0 flex-wrap items-center gap-2">
                        {{-- Island 1: language / translation --}}
                        @if ($hasEn)
                            <div class="island">
                                <flux:button size="xs" :variant="$viewLang === 'ar' ? 'primary' : 'ghost'" wire:click="setViewLang('ar')">{{ __('Arabic') }}</flux:button>
                                <flux:button size="xs" :variant="$viewLang === 'en' ? 'primary' : 'ghost'" wire:click="setViewLang('en')">EN</flux:button>
                                <flux:button size="xs" variant="ghost"
                                    wire:click="translate('en')"
                                    wire:loading.attr="disabled"
                                    wire:target="translate"
                                    icon="arrow-path"
                                    title="{{ __('Regenerate English translation') }}"></flux:button>
                            </div>
                        @else
                            <flux:button size="sm" variant="ghost"
                                wire:click="translate('en')"
                                wire:loading.attr="disabled"
                                wire:target="translate"
                                icon="language">
                                <span wire:loading.remove wire:target="translate">{{ __('Translate to EN') }}</span>
                                <span wire:loading wire:target="translate">{{ __('Translating…') }}</span>
                            </flux:button>
                        @endif

                        {{-- Island 2: document I/O --}}
                        <div class="island">
                            <flux:button size="xs" variant="ghost"
                                wire:click="save"
                                wire:loading.attr="disabled"
                                wire:target="save"
                                icon="check"
                                title="{{ __('Save') }}"></flux:button>
                            <flux:button size="xs" variant="ghost"
                                wire:click="downloadDocx"
                                wire:loading.attr="disabled"
                                wire:target="downloadDocx"
                                icon="arrow-down-tray"
                                title="{{ __('.docx') }}">DOCX</flux:button>
                            <flux:button size="xs" variant="ghost"
                                wire:click="downloadPdf"
                                wire:loading.attr="disabled"
                                wire:target="downloadPdf"
                                icon="document-arrow-down"
                                title="{{ __('.pdf') }}">PDF</flux:button>
                            @if ($hasEn)
                                <flux:button size="xs" variant="ghost"
                                    wire:click="downloadDocx('en')"
                                    wire:loading.attr="disabled"
                                    wire:target="downloadDocx"
                                    icon="arrow-down-tray"
                                    title="{{ __('.docx (bilingual)') }}">EN.docx</flux:button>
                                <flux:button size="xs" variant="ghost"
                                    wire:click="downloadPdf('en')"
                                    wire:loading.attr="disabled"
                                    wire:target="downloadPdf"
                                    icon="document-arrow-down"
                                    title="{{ __('.pdf (bilingual)') }}">EN.pdf</flux:button>
                            @endif
                            @if (is_array($c->body_history) && count($c->body_history) > 0)
                                <flux:button size="xs" :variant="$showHistory ? 'primary' : 'ghost'" wire:click="toggleHistory" icon="clock" title="{{ __('History') }}"></flux:button>
                            @endif
                        </div>

                        {{-- Island 3: status transitions / destructive --}}
                        @if ($c->status !== 'finalized')
                            <flux:button size="sm" variant="primary"
                                wire:click="finalize"
                                wire:loading.attr="disabled"
                                wire:target="finalize"
                                icon="check-badge">
                                <span wire:loading.remove wire:target="finalize">{{ __('Finalize') }}</span>
                                <span wire:loading wire:target="finalize">{{ __('Finalizing…') }}</span>
                            </flux:button>
                        @else
                            <span class="pill pill-success" title="{{ __('Finalized — ready to send') }}">
                                <flux:icon.check-badge class="size-3.5" /> {{ __('Finalized') }}
                            </span>
                        @endif
                        <flux:button size="sm" variant="danger"
                            wire:click="delete({{ $c->id }})"
                            wire:loading.attr="disabled"
                            wire:target="delete"
                            wire:confirm="{{ __('Delete this contract?') }}"
                            icon="trash"
                            title="{{ __('Delete') }}"></flux:button>
                    </div>
                </header>

                {{-- Needs-review banner: surfaces when CorpusCitationGate
                     flagged unverified citations during the draft or last
                     edit. The lawyer sees this even when the citation panel
                     isn't visible on the smaller viewports. --}}
                @php($_gate = $this->corpusGate)
                @if ($c->status === 'needs_review' && $_gate)
                    <div class="flex items-start gap-3 border-b border-rose-200/70 bg-rose-50 px-4 py-3 text-sm text-rose-900 md:px-6 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-200">
                        <flux:icon.exclamation-triangle class="mt-0.5 size-5 flex-shrink-0" />
                        <div class="flex-1">
                            <p class="font-semibold">{{ __('This draft cites articles we could not verify in the corpus.') }}</p>
                            <p class="mt-0.5 text-[12.5px] opacity-90">
                                {{ __(':n unverified, :u uncertain · :p% confidence. Review the citations panel before finalizing.', [
                                    'n' => $_gate['unverified'] ?? 0,
                                    'u' => $_gate['uncertain'] ?? 0,
                                    'p' => $_gate['confidence_pct'] ?? 0,
                                ]) }}
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Ready-to-send banner: replaces the dead-end after Finalize
                     with a clear next-action set (download, share, archive). --}}
                @if ($c->status === 'finalized')
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-emerald-200/70 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 md:px-6 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                        <div class="flex items-start gap-3">
                            <flux:icon.check-badge class="mt-0.5 size-5 flex-shrink-0" />
                            <div>
                                <p class="font-semibold">{{ __('Contract finalized. Ready to send.') }}</p>
                                <p class="mt-0.5 text-[12.5px] opacity-90">
                                    {{ __('Editing is still allowed for typo fixes — saves create a new version in history.') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:button size="xs" variant="primary"
                                wire:click="downloadPdf"
                                wire:loading.attr="disabled"
                                wire:target="downloadPdf"
                                icon="document-arrow-down">{{ __('Download .pdf') }}</flux:button>
                            <flux:button size="xs" variant="ghost"
                                wire:click="downloadDocx"
                                wire:loading.attr="disabled"
                                wire:target="downloadDocx"
                                icon="arrow-down-tray">{{ __('Download .docx') }}</flux:button>
                            @if ($hasEn)
                                <flux:button size="xs" variant="ghost"
                                    wire:click="downloadDocx('en')"
                                    wire:loading.attr="disabled"
                                    wire:target="downloadDocx"
                                    icon="arrow-down-tray">{{ __('Bilingual .docx') }}</flux:button>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Editor + Citation panel --}}
                <div class="flex flex-1 overflow-hidden">
                    {{-- Document body --}}
                    @php($showBilingual = $viewLang !== 'ar' && isset($translations[$viewLang]))
                    @if ($showBilingual)
                        @php($t = $translations[$viewLang])
                        {{-- Bilingual layout: stacked on mobile (each pane scrolls
                             individually within an outer scroll), side-by-side
                             from md: up. The mobile order keeps Arabic FIRST
                             (binding source), translation second (working aid). --}}
                        <div class="flex flex-1 flex-col overflow-y-auto md:flex-row md:overflow-hidden surface-paper">
                            {{-- Arabic source --}}
                            <div class="md:flex-1 md:overflow-y-auto" dir="rtl">
                                <div class="px-5 py-7 md:px-8 md:py-10">
                                    <span class="eyebrow-tag">{{ __('Arabic — legally binding') }}</span>
                                    <textarea
                                        wire:model.live.debounce.500ms="editBody"
                                        class="mt-5 block min-h-[40vh] w-full resize-none border-0 bg-transparent font-serif text-[15px] leading-[2.05] text-zinc-900 outline-none md:min-h-[60vh] dark:text-zinc-100"></textarea>
                                </div>
                            </div>

                            {{-- Divider: horizontal on mobile, vertical on desktop --}}
                            <div class="divider-soft hidden md:block md:my-8" aria-hidden="true"></div>
                            <div class="mx-5 h-px bg-zinc-200 md:hidden dark:bg-zinc-800" aria-hidden="true"></div>

                            {{-- Translation (working aid) --}}
                            <div class="md:flex-1 md:overflow-y-auto" dir="ltr">
                                <div class="px-5 py-7 md:px-8 md:py-10">
                                    <div class="mb-5 flex flex-wrap items-center justify-between gap-2">
                                        <span class="eyebrow-tag">
                                            {{ strtoupper($viewLang) }} · {{ __('— working translation, not legally binding') }}
                                        </span>
                                        <span class="font-mono text-[10px] uppercase tracking-wider text-zinc-400 tabular-nums">
                                            {{ __('My-lawyer AI') }} · {{ \Illuminate\Support\Carbon::parse($t['generated_at'] ?? now())->diffForHumans() }}
                                        </span>
                                    </div>
                                    @if (! empty($t['warnings']))
                                        <div class="notice-row mb-5">
                                            <flux:icon.exclamation-triangle class="size-4 flex-shrink-0 mt-0.5" />
                                            <div class="flex-1">
                                                <p class="font-medium">{{ __(':n term(s) need lawyer review:', ['n' => count($t['warnings'])]) }}</p>
                                                <ul class="mt-1 flex flex-wrap gap-1.5">
                                                    @foreach (array_slice($t['warnings'], 0, 6) as $w)
                                                        <li class="rounded bg-white/60 px-1.5 py-0.5 font-mono text-[10px] dark:bg-black/20">{{ $w }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="text-prose whitespace-pre-wrap break-words">{{ $t['body'] ?? '' }}</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex-1 overflow-y-auto surface-paper">
                            <div class="mx-auto max-w-3xl px-5 py-7 md:px-8 md:py-10">
                                <textarea
                                    wire:model.live.debounce.500ms="editBody"
                                    class="block min-h-[60vh] w-full resize-none border-0 bg-transparent font-serif text-[15px] leading-[2.05] text-zinc-900 outline-none dark:text-zinc-100"></textarea>
                            </div>
                        </div>
                    @endif

                    {{-- Version history panel --}}
                    @if ($showHistory && is_array($c->body_history) && count($c->body_history) > 0)
                        <aside class="hidden w-96 flex-shrink-0 flex-col overflow-hidden border-s hairline bg-zinc-50/50 dark:bg-zinc-950/30 lg:flex">
                            <div class="flex items-center justify-between border-b hairline px-5 py-3">
                                <span class="eyebrow-tag">{{ __('Version history') }}</span>
                                <flux:button size="xs" variant="ghost" wire:click="toggleHistory" icon="x-mark"></flux:button>
                            </div>
                            <ul class="border-b hairline px-2 py-2">
                                @foreach ($c->body_history as $idx => $entry)
                                    @php($_isAiEdit = ($entry['saved_by'] ?? '') === 'AI edit')
                                    <li>
                                        <button wire:click="selectVersion({{ $idx }})"
                                            class="block w-full rounded-md px-3 py-2 text-start transition
                                            {{ $compareIdx === $idx ? 'bg-[var(--color-parchment)] dark:bg-zinc-800' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800/50' }}">
                                            <div class="flex items-baseline justify-between gap-2">
                                                <div class="flex items-baseline gap-1.5">
                                                    <span class="font-mono text-xs font-semibold tabular-nums">v{{ $entry['version'] ?? '?' }}</span>
                                                    @if ($_isAiEdit)
                                                        <span class="pill pill-info" style="font-size:9px;padding:1px 6px;">{{ __('AI') }}</span>
                                                    @endif
                                                </div>
                                                <span class="text-[10px] text-zinc-500 tabular-nums">{{ \Illuminate\Support\Carbon::parse($entry['saved_at'] ?? now())->diffForHumans() }}</span>
                                            </div>
                                            <span class="block truncate text-[11px] text-zinc-500">{{ $entry['saved_by'] ?? '—' }}</span>
                                            @if (! empty($entry['edit_request']))
                                                <span class="mt-0.5 block truncate text-[10.5px] italic text-zinc-600 dark:text-zinc-400" title="{{ $entry['edit_request'] }}">
                                                    "{{ \Illuminate\Support\Str::limit($entry['edit_request'], 60) }}"
                                                </span>
                                            @endif
                                        </button>
                                    </li>
                                @endforeach
                            </ul>

                            {{-- Diff body --}}
                            @if ($this->diff !== null)
                                @php($d = $this->diff)
                                <div class="border-b hairline px-5 py-2.5 text-[11px]">
                                    <span class="text-[var(--color-success)] font-mono tabular-nums">+{{ $d['added'] }}</span>
                                    <span class="ms-2 text-[var(--color-danger)] font-mono tabular-nums">−{{ $d['removed'] }}</span>
                                    <span class="ms-2 text-zinc-400 font-mono tabular-nums">·{{ $d['unchanged'] }} {{ __('unchanged') }}</span>
                                </div>
                                <div class="flex-1 overflow-y-auto px-3 py-3 space-y-1.5 font-mono text-[11.5px] leading-relaxed">
                                    @foreach ($d['rows'] as $row)
                                        @if ($row['type'] === 'add')
                                            <div class="rounded border-l-2 border-l-[var(--color-success)] bg-[var(--color-success-soft)]/40 px-2 py-1.5 text-zinc-800 dark:text-zinc-200">
                                                <span class="me-1 text-[var(--color-success)] font-bold">+</span>{{ \Illuminate\Support\Str::limit($row['text'], 200) }}
                                            </div>
                                        @elseif ($row['type'] === 'remove')
                                            <div class="rounded border-l-2 border-l-[var(--color-danger)] bg-[var(--color-danger-soft)]/40 px-2 py-1.5 text-zinc-800 line-through opacity-70 dark:text-zinc-200">
                                                <span class="me-1 text-[var(--color-danger)] font-bold no-underline">−</span>{{ \Illuminate\Support\Str::limit($row['text'], 200) }}
                                            </div>
                                        @else
                                            <div class="px-2 py-1 text-zinc-400 dark:text-zinc-600">
                                                <span class="me-1">·</span>{{ \Illuminate\Support\Str::limit($row['text'], 100) }}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="flex h-full items-center justify-center p-8">
                                    <p class="text-center text-xs text-zinc-500">{{ __('Pick a version to compare against the current body.') }}</p>
                                </div>
                            @endif
                        </aside>
                    @endif

                    {{-- Citation audit panel — combines two sources:
                         (1) Marker audit:  [n] markers vs chunks the model saw via lookup_law
                         (2) Corpus gate:   "Article N of Law Y" patterns vs the indexed corpus
                         Shown whenever EITHER has data. --}}
                    @if ($this->auditRows->isNotEmpty() || $this->corpusGate)
                        {{-- Citation panel: visible from `lg` up (was `xl`).
                             On 13" laptops the needs-review banner alone
                             wasn't enough — lawyers want the full citation
                             breakdown visible while editing. --}}
                        <aside class="hidden w-80 flex-shrink-0 flex-col overflow-hidden border-s hairline bg-zinc-50/50 dark:bg-zinc-950/30 lg:flex xl:w-96">
                            <div class="border-b hairline px-5 py-3">
                                <p class="eyebrow">{{ __('Citation audit') }}</p>
                                @if ($this->auditRows->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                        <span class="pill pill-success">{{ $s['verified'] ?? 0 }} {{ __('verified') }}</span>
                                        <span class="pill pill-warning">{{ $s['uncertain'] ?? 0 }} {{ __('uncertain') }}</span>
                                        <span class="pill pill-danger">{{ $s['unverified'] ?? 0 }} {{ __('unverified') }}</span>
                                    </div>
                                @endif

                                {{-- Corpus gate sub-section --}}
                                @if ($this->corpusGate)
                                    <div class="mt-3 rounded-md border hairline bg-white p-3 dark:bg-zinc-900">
                                        <div class="mb-1.5 flex items-center justify-between gap-2">
                                            <p class="font-mono text-[10px] uppercase tracking-wider text-zinc-500">{{ __('Corpus gate') }}</p>
                                            @php($_gateOk = ($_gate['passes'] ?? false))
                                            <span class="{{ $_gateOk ? 'pill pill-success' : 'pill pill-danger' }}">
                                                {{ $_gateOk ? __('pass') : __('needs review') }}
                                                · {{ $_gate['confidence_pct'] ?? 0 }}%
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="pill pill-success">{{ $_gate['verified'] ?? 0 }} {{ __('verified') }}</span>
                                            <span class="pill pill-warning">{{ $_gate['uncertain'] ?? 0 }} {{ __('uncertain') }}</span>
                                            <span class="pill pill-danger">{{ $_gate['unverified'] ?? 0 }} {{ __('unverified') }}</span>
                                        </div>

                                        @php($_unv = $_gate['unverified_citations'] ?? [])
                                        @php($_unc = $_gate['uncertain_citations'] ?? [])
                                        @if (! empty($_unv))
                                            <details class="mt-2.5" open>
                                                <summary class="cursor-pointer font-mono text-[10px] uppercase tracking-wider text-rose-700 dark:text-rose-400">{{ __('Unverified citations') }} ({{ count($_unv) }})</summary>
                                                <ul class="mt-1.5 space-y-1.5">
                                                    @foreach ($_unv as $row)
                                                        <li class="rounded border border-rose-200 bg-rose-50/60 px-2 py-1.5 text-[11px] dark:border-rose-900/60 dark:bg-rose-950/40">
                                                            <p class="font-mono text-rose-900 dark:text-rose-200">{{ $row['raw'] ?? '' }}</p>
                                                            @if (! empty($row['article']))
                                                                <p class="mt-0.5 text-[10px] text-rose-700/80 dark:text-rose-300/80">{{ __('article') }}: {{ $row['article'] }}</p>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </details>
                                        @endif
                                        @if (! empty($_unc))
                                            <details class="mt-1.5">
                                                <summary class="cursor-pointer font-mono text-[10px] uppercase tracking-wider text-amber-700 dark:text-amber-400">{{ __('Uncertain citations') }} ({{ count($_unc) }})</summary>
                                                <ul class="mt-1.5 space-y-1.5">
                                                    @foreach ($_unc as $row)
                                                        <li class="rounded border border-amber-200 bg-amber-50/60 px-2 py-1.5 text-[11px] dark:border-amber-900/60 dark:bg-amber-950/40">
                                                            <p class="font-mono text-amber-900 dark:text-amber-200">{{ $row['raw'] ?? '' }}</p>
                                                            <p class="mt-0.5 text-[10px] text-amber-700/80 dark:text-amber-300/80">{{ __('score') }}: {{ $row['score'] ?? 0 }}/100</p>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </details>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <ul class="flex-1 overflow-y-auto px-4 py-3 space-y-3">
                                @foreach ($this->auditRows as $row)
                                    @php(
                                        [$pillClass, $borderClass] = match($row['verdict'] ?? 'unverified') {
                                            'verified' => ['pill pill-success', 'border-l-[var(--color-success)]'],
                                            'uncertain' => ['pill pill-warning', 'border-l-[var(--color-warning)]'],
                                            default => ['pill pill-danger', 'border-l-[var(--color-danger)]'],
                                        }
                                    )
                                    @php($chunk = $this->citationChunks->get($row['chunk_id']))
                                    <li class="rounded-md border-l-4 hairline border bg-white p-3 dark:bg-zinc-900 {{ $borderClass }}">
                                        <div class="mb-2 flex items-center justify-between gap-2">
                                            <span class="font-mono text-xs font-semibold text-zinc-700 dark:text-zinc-300">[{{ $row['marker'] ?? '?' }}]</span>
                                            <div class="flex items-center gap-1.5">
                                                <span class="{{ $pillClass }}">{{ $row['verdict'] ?? 'unverified' }}</span>
                                                <span class="font-mono text-[10px] text-zinc-500 tabular-nums">{{ number_format((float) ($row['score'] ?? 0), 2) }}</span>
                                            </div>
                                        </div>
                                        <p class="mb-1 text-xs font-medium text-zinc-900 dark:text-zinc-200 truncate">{{ $row['document_title'] ?? __('No matching source') }}</p>
                                        <p class="mb-2 text-[11px] italic text-zinc-600 dark:text-zinc-400">"{{ \Illuminate\Support\Str::limit($row['snippet'] ?? '', 140) }}"</p>
                                        @if ($chunk)
                                            <div class="rounded bg-[var(--color-parchment)] p-2 text-[11px] leading-relaxed text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                                {{ \Illuminate\Support\Str::limit($chunk->content, 220) }}
                                            </div>
                                            @if (! is_null($row['document_version'] ?? null))
                                                <p class="mt-1.5 font-mono text-[9px] uppercase tracking-wider text-zinc-400">v{{ $row['document_version'] }}</p>
                                            @endif
                                        @else
                                            <p class="text-[11px] text-[var(--color-danger)]">{{ __('No source chunk matched.') }}</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </aside>
                    @endif
                </div>
            </div>
        @endif
    </main>
</div>
