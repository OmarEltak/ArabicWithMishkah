<?php

use App\Models\LegalDocument;
use App\Services\Ingestion\LawSearchService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Search Laws')] class extends Component
{
    #[Url(as: 'q')]
    public string $query = '';

    #[Url(as: 'j')]
    public string $jurisdiction = '';

    #[Url(as: 'cat')]
    public string $category = '';

    #[Url(as: 'per')]
    public int $pageSize = 10;

    public bool $hasSearched = false;

    public int $totalLocal = 0;

    public ?int $totalUpstream = null;

    public int $triggeredIngest = 0;

    public int $pendingCount = 0;

    public bool $cacheHit = false;

    /** @var array<int, array{document: LegalDocument, score: float, snippet: string, origin: string}> */
    public array $results = [];

    /** Map of eastlaws country_id → ISO code, used to translate jurisdiction filter to a country_id for upstream peek. */
    private const ISO_TO_COUNTRY_ID = [
        'EG' => 1, 'JO' => 2, 'AE' => 4, 'KW' => 5,
        'BH' => 6, 'QA' => 7, 'SA' => 9, 'OM' => 10,
        'LY' => 11, 'TN' => 12, 'LB' => 19,
    ];

    public function mount(): void
    {
        // If the URL already carries a query (deep-linked or back-button),
        // run it so the page hydrates with results immediately.
        if (trim($this->query) !== '') {
            $this->runSearch();
        }
    }

    public function search(): void
    {
        $this->runSearch();
    }

    private function runSearch(): void
    {
        $query = trim($this->query);
        if (mb_strlen($query) < 2) {
            $this->results = [];
            $this->hasSearched = false;

            return;
        }

        $countryId = self::ISO_TO_COUNTRY_ID[$this->jurisdiction] ?? 1;
        $outcome = app(LawSearchService::class)->search(
            rawQuery: $query,
            filters: [
                'jurisdiction' => $this->jurisdiction ?: null,
                'category' => $this->category ?: null,
                'country_id' => $countryId,
            ],
            pageSize: $this->pageSize,
        );

        $this->results = $outcome['results'];
        $this->totalLocal = $outcome['total_local'];
        $this->totalUpstream = $outcome['total_upstream'];
        $this->triggeredIngest = $outcome['triggered_ingest'];
        $this->pendingCount = (int) ($outcome['pending_count'] ?? 0);
        $this->cacheHit = (bool) ($outcome['cache']['hit'] ?? false);
        $this->hasSearched = true;
    }

    /**
     * Polled by wire:poll while at least one row is still being fetched and
     * indexed. Re-runs the search so newly-completed documents surface.
     */
    public function refreshIfPending(): void
    {
        if ($this->pendingCount === 0) {
            return;
        }
        $this->runSearch();
    }

    #[Computed]
    public function jurisdictionOptions(): array
    {
        return [
            '' => __('All jurisdictions'),
            'EG' => 'EG — مصر',
            'SA' => 'SA — السعودية',
            'AE' => 'AE — الإمارات',
            'KW' => 'KW — الكويت',
            'QA' => 'QA — قطر',
            'BH' => 'BH — البحرين',
            'OM' => 'OM — سلطنة عمان',
            'JO' => 'JO — الأردن',
            'LB' => 'LB — لبنان',
            'TN' => 'TN — تونس',
            'LY' => 'LY — ليبيا',
        ];
    }

    #[Computed]
    public function categoryOptions(): array
    {
        $cats = (array) config('eastlaws_snapshot.categories', []);

        return array_merge(['' => __('All categories')], $cats);
    }
}; ?>

<div class="mx-auto w-full max-w-7xl flex-col gap-8 px-6 py-8"
     @if ($pendingCount > 0) wire:poll.2s="refreshIfPending" @endif>

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4 border-b hairline pb-8">
        <div class="min-w-0">
            <span class="eyebrow-tag">{{ __('Search Laws') }}</span>
            <h1 class="display mt-3 text-3xl text-zinc-900 dark:text-zinc-50">{{ __('Find legal authority') }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                {{ __('Search the legal corpus. When results are thin we consult the upstream legal database and queue new documents for indexing automatically.') }}
            </p>
        </div>
    </div>

    {{-- Search form --}}
    <form wire:submit.prevent="search" class="mt-6 card p-6 space-y-4">
        <flux:input wire:model="query" :label="__('Query')" placeholder="قانون الشركات" autofocus />
        <div class="grid gap-4 md:grid-cols-3">
            <flux:field>
                <flux:label>{{ __('Jurisdiction') }}</flux:label>
                <flux:select wire:model="jurisdiction">
                    @foreach ($this->jurisdictionOptions as $code => $label)
                        <flux:select.option value="{{ $code }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Category') }}</flux:label>
                <flux:select wire:model="category">
                    @foreach ($this->categoryOptions as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>{{ __('Results per page') }}</flux:label>
                <flux:select wire:model="pageSize">
                    <flux:select.option value="10">10</flux:select.option>
                    <flux:select.option value="25">25</flux:select.option>
                    <flux:select.option value="50">50</flux:select.option>
                </flux:select>
            </flux:field>
        </div>
        <div class="flex justify-end">
            <flux:button type="submit"
                variant="primary"
                icon="magnifying-glass"
                wire:loading.attr="disabled"
                wire:target="search">
                <span wire:loading.remove wire:target="search">{{ __('Search') }}</span>
                <span wire:loading wire:target="search">{{ __('Searching…') }}</span>
            </flux:button>
        </div>
    </form>

    {{-- Status bar --}}
    @if ($this->hasSearched)
        <div class="mt-6 flex flex-wrap items-center gap-3 text-xs text-zinc-500">
            <span>{{ __(':n results', ['n' => count($this->results)]) }}</span>
            @if ($pendingCount > 0)
                <span class="text-zinc-300 dark:text-zinc-700">•</span>
                <span class="pill pill-info">
                    <flux:icon.arrow-path class="size-3 animate-spin" />
                    {{ __('Finding :n more…', ['n' => $pendingCount]) }}
                </span>
            @endif
        </div>
    @endif

    {{-- Results --}}
    @if ($this->hasSearched)
        <div class="mt-4">
            @if (count($this->results) === 0)
                <div class="rounded-md border border-dashed hairline p-12 text-center">
                    @if ($pendingCount > 0)
                        <flux:icon.arrow-path class="mx-auto size-10 animate-spin text-zinc-300 dark:text-zinc-700" />
                        <p class="mt-3 text-sm text-zinc-500">
                            {{ __('Fetching :n laws from the legal database… results will appear here as they finish indexing.', ['n' => $pendingCount]) }}
                        </p>
                    @else
                        <flux:icon.magnifying-glass class="mx-auto size-10 text-zinc-300 dark:text-zinc-700" />
                        <p class="mt-3 text-sm text-zinc-500">
                            {{ __('No matching authority found. Try a different query or broaden the jurisdiction filter.') }}
                        </p>
                    @endif
                </div>
            @else
                <div class="overflow-hidden rounded-lg border hairline bg-white dark:bg-zinc-900">
                    <table class="w-full text-sm">
                        <thead class="border-b hairline bg-zinc-50/50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-950/30">
                            <tr class="text-start">
                                <th class="px-4 py-3 text-start font-medium">{{ __('Title') }}</th>
                                <th class="px-4 py-3 text-start font-medium">{{ __('Category') }}</th>
                                <th class="px-4 py-3 text-start font-medium">{{ __('Verified') }}</th>
                                <th class="px-4 py-3 text-start font-medium">{{ __('Version') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->results as $row)
                                @php($doc = $row['document'])
                                <tr class="border-t hairline transition hover:bg-[var(--color-paper-edge)]/40 dark:hover:bg-zinc-950/30">
                                    <td class="px-4 py-3 max-w-md">
                                        <a href="{{ route('lawyer.law-show', ['docId' => $doc->id]) }}" wire:navigate
                                           class="block group">
                                            <p class="font-medium text-zinc-900 group-hover:text-[var(--color-accent)] dark:text-zinc-100 dark:group-hover:text-[var(--color-accent)] line-clamp-1">
                                                {{ $doc->title }}
                                            </p>
                                            @if ($doc->jurisdiction)
                                                <p class="mt-0.5 text-[11px] text-zinc-500">{{ $doc->jurisdiction }}</p>
                                            @endif
                                            @if ($row['snippet'] !== '')
                                                <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400 line-clamp-2">{{ $row['snippet'] }}</p>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($doc->category)
                                            <span class="rounded-md bg-[var(--color-parchment)] px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-[var(--color-accent-content)] dark:bg-zinc-800 dark:text-zinc-300">{{ $doc->category }}</span>
                                        @else
                                            <span class="text-zinc-400">—</span>
                                        @endif
                                    </td>
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
                                            <span class="text-zinc-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs text-zinc-600 dark:text-zinc-400 tabular-nums">v{{ $doc->version ?? 1 }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>
