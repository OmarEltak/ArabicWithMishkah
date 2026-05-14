<?php

use App\Models\AuditLog;
use App\Models\ChatSession;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\LegalDocument;
use App\Services\AI\UsageTracker;
use App\Services\Contracts\ContractDraftingService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public bool $sampleBusy = false;

    /**
     * Pre-canned corporate-draft showcase. Fires an Egyptian shareholders
     * agreement against a fully-populated intent and redirects to the
     * resulting session. Designed for a "see it work in 10 seconds"
     * demo experience — no typing required.
     */
    /**
     * Sample-draft demo bank. Each entry maps a "kind" key → [template slug,
     * pre-populated Arabic intent]. The sample is finalised end-to-end so the
     * lawyer's first 10 seconds in the product show real output, not a blank
     * form. Add new samples here when you ship new templates worth showcasing.
     */
    private const SAMPLES = [
        'corporate' => [
            'template' => 'eg-shareholders-agreement',
            'intent' => 'عقد شركاء بين السيد محمد سيف الدين والسيد طارق فهمي لتأسيس شركة "تك جلوبال للحلول الرقمية" — شركة ذات مسؤولية محدودة برأس مال قدره 1,000,000 جنيه مصري. محمد سيف الدين: حصة 60%. طارق فهمي: حصة 40%. النشاط: تطوير برمجيات وحلول تقنية. تاريخ التأسيس: اليوم. مكان النشاط: القاهرة. نريد بنود قياسية للحوكمة، حق الشفعة، عدم المنافسة، والتحكيم.',
        ],
        'saas' => [
            'template' => 'eg-saas-subscription',
            'intent' => 'عقد اشتراك SaaS بين شركة Acme Cloud Services (مقدم الخدمة) ومجموعة الفيصل التجارية (العميل) لاستخدام منصة "نظام إدارة المخزون السحابي" لمدة 12 شهراً برسوم شهرية 5,000 جنيه مصري. يخضع للقانون المصري وقانون حماية البيانات الشخصية 151/2020. توافر 99% شهرياً، دعم فني خلال ساعات العمل، حماية البيانات وفقاً للقانون المصري.',
        ],
        'dpa' => [
            'template' => 'eg-dpa',
            'intent' => 'اتفاقية معالجة بيانات شخصية بين شركة الفيصل القابضة (المتحكم) وشركة Acme Cloud Services (المعالج) — استضافة بيانات العملاء وتشغيل منصة CRM السحابية، تشمل: الاسم، البريد الإلكتروني، رقم الهاتف، العنوان، سجل الطلبات. يخضع لقانون 151/2020 ولائحته التنفيذية.',
        ],
        'employment' => [
            'template' => 'eg-employment',
            'intent' => 'عقد عمل فردي بين شركة الفيصل التجارية (صاحب العمل) والسيد أحمد محمود السيد (العامل). الوظيفة: مدير تطوير الأعمال. تاريخ بدء العمل: اليوم. الراتب الشهري: 25,000 جنيه مصري. فترة الاختبار: 3 أشهر. مقر العمل: القاهرة. ساعات العمل: 8 ساعات يومياً، 5 أيام أسبوعياً.',
        ],
    ];

    public function trySample(ContractDraftingService $svc, string $kind = 'corporate'): void
    {
        $this->sampleBusy = true;
        try {
            $sample = self::SAMPLES[$kind] ?? null;
            if (! $sample) {
                Flux::toast(variant: 'danger', text: __('Unknown sample kind.'));

                return;
            }
            $template = ContractTemplate::where('slug', $sample['template'])->first();
            if (! $template) {
                Flux::toast(variant: 'danger', text: __('Sample template missing — re-run the seeder.'));

                return;
            }

            $session = $svc->startSession(Auth::user(), $template, $sample['intent']);
            $svc->finalize($session->fresh());

            $this->redirect(route('lawyer.chat', ['session' => $session->id]));
        } catch (Throwable $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        } finally {
            $this->sampleBusy = false;
        }
    }

    /** Back-compat alias used by the existing primary CTA in the hero header. */
    public function tryCorporateSample(ContractDraftingService $svc): void
    {
        $this->trySample($svc, 'corporate');
    }

    /**
     * Official-corpus aggregates (count + stale) shared across multiple
     * computed properties. Cached at the user/global level for 5 min;
     * the corpus only changes on ingestion-job completion, so a 5-min
     * staleness window is acceptable and the cache saves 2 full-table
     * scans per dashboard render.
     *
     * @return array{total:int, stale:int}
     */
    #[Computed]
    public function corpusAggregates(): array
    {
        return cache()->remember('dashboard.corpus_aggregates', 300, function (): array {
            $official = (array) config('legal_sources.official_slugs', ['eastlaws']);
            $total = LegalDocument::query()->whereIn('source', $official)->count();
            $stale = LegalDocument::query()
                ->whereIn('source', $official)
                ->where(function ($q) {
                    $q->whereNull('next_check_at')->orWhere('next_check_at', '<=', now());
                })
                ->count();

            return ['total' => $total, 'stale' => $stale];
        });
    }

    #[Computed]
    public function counts(): array
    {
        $userId = Auth::id();
        $userScopedDocs = LegalDocument::query()
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->count();

        // Roll up Contract aggregates in one query (count + finalized) so
        // we don't scan the contracts table twice per render.
        $contractRow = Contract::query()
            ->where('user_id', $userId)
            ->selectRaw("COUNT(*) AS total, SUM(CASE WHEN status = 'finalized' THEN 1 ELSE 0 END) AS finalised")
            ->first();

        return [
            'documents' => $userScopedDocs,
            'official' => $this->corpusAggregates['total'],
            'sessions' => ChatSession::query()->where('user_id', $userId)->count(),
            'contracts' => (int) ($contractRow->total ?? 0),
            'finalised' => (int) ($contractRow->finalised ?? 0),
        ];
    }

    /**
     * Top categories present in the official statutory corpus. Surfaces the
     * "moat shape" at a glance — the demo viewer sees jurisdictional +
     * domain coverage instead of just a doc count.
     *
     * @return array<int, array{category:string, count:int}>
     */
    #[Computed]
    public function topCategories(): array
    {
        $official = (array) config('legal_sources.official_slugs', ['eastlaws']);

        return LegalDocument::query()
            ->whereIn('source', $official)
            ->whereNotNull('category')
            ->selectRaw('category, COUNT(*) as c')
            ->groupBy('category')
            ->orderByDesc('c')
            ->limit(8)
            ->get()
            ->map(fn ($r) => ['category' => (string) $r->category, 'count' => (int) $r->c])
            ->all();
    }

    /**
     * Freshness health — how many official-source docs are stale
     * (next_check_at <= now or null).
     */
    #[Computed]
    public function freshness(): array
    {
        $agg = $this->corpusAggregates;
        $total = $agg['total'];
        $stale = $agg['stale'];

        return [
            'total' => $total,
            'stale' => $stale,
            'fresh' => max(0, $total - $stale),
            'pct' => $total > 0 ? (int) round(($total - $stale) / $total * 100) : 100,
        ];
    }

    #[Computed]
    public function spend(): array
    {
        return app(UsageTracker::class)->dashboard();
    }

    #[Computed]
    public function recentContracts()
    {
        return Contract::query()->where('user_id', Auth::id())->latest('id')->limit(5)->get();
    }

    #[Computed]
    public function recentActivity()
    {
        return AuditLog::query()
            ->where(function ($q) {
                $q->where('user_id', Auth::id())->orWhereNull('user_id');
            })
            ->latest('id')
            ->limit(8)
            ->get();
    }
}; ?>

<div class="flex w-full flex-1 flex-col gap-8">

    {{-- Hero header --}}
    <div class="flex flex-wrap items-end justify-between gap-4 border-b hairline pb-8">
        <div class="min-w-0">
            <span class="eyebrow-tag">{{ __('Operations overview') }}</span>
            <h1 class="display mt-3 text-4xl text-zinc-900 dark:text-zinc-50">
                {{ __('Welcome back, :name', ['name' => str(auth()->user()->name)->before(' ')]) }}
            </h1>
            <p class="mt-2 max-w-xl text-sm text-zinc-600 dark:text-zinc-400">
                {{ __("Today's health of your knowledge base, drafting activity, and AI spend at a glance.") }}
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <flux:button wire:click="tryCorporateSample" :disabled="$sampleBusy" variant="primary" icon="sparkles">
                {{ $sampleBusy ? __('Drafting…') : __('Try a sample draft') }}
            </flux:button>
            <div class="island">
                <flux:button size="xs" :href="route('lawyer.chat')" variant="ghost" wire:navigate icon="pencil-square">{{ __('Draft') }}</flux:button>
                <flux:button size="xs" :href="route('lawyer.knowledge')" variant="ghost" wire:navigate icon="building-library">{{ __('Knowledge base') }}</flux:button>
            </div>
        </div>
    </div>

    @php($k = $this->counts)
    @php($isFirstRun = $k['sessions'] === 0 && $k['contracts'] === 0)

    {{-- First-run welcome card. Shows only until the user has either a
         chat session or a contract — then this whole block disappears
         and the KPI strip becomes the primary thing on the page. --}}
    @if ($isFirstRun)
        <div class="bezel" wire:key="first-run-card">
            <div class="bezel-inner p-7 md:p-9">
                <div class="grid gap-7 lg:grid-cols-[1.2fr_1fr] lg:items-start">
                    <div>
                        <span class="eyebrow-tag">{{ __('Get started') }}</span>
                        <h2 class="display mt-3 text-2xl text-zinc-900 dark:text-zinc-50 md:text-3xl">
                            {{ __('You\'re all set — what would you like to do first?') }}
                        </h2>
                        <p class="mt-3 max-w-lg text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                            {{ __('My-lawyer drafts bilingual, citation-grounded contracts under MENA jurisdictions. Pick a path below — your first draft takes about a minute.') }}
                        </p>
                        <div class="mt-6 flex flex-wrap items-center gap-2">
                            <flux:button wire:click="tryCorporateSample"
                                :disabled="$sampleBusy"
                                wire:loading.attr="disabled"
                                wire:target="tryCorporateSample,trySample"
                                variant="primary"
                                icon="sparkles">
                                <span wire:loading.remove wire:target="tryCorporateSample,trySample">{{ __('See a sample draft (10 sec)') }}</span>
                                <span wire:loading wire:target="tryCorporateSample,trySample">{{ __('Drafting…') }}</span>
                            </flux:button>
                            <flux:button :href="route('lawyer.chat')" wire:navigate variant="ghost" icon="pencil-square">
                                {{ __('Start a blank draft') }}
                            </flux:button>
                        </div>
                    </div>
                    <ol class="space-y-3.5 border-l hairline ps-5 text-sm md:ps-6">
                        @foreach ([
                            [__('Describe the deal'), __('In a sentence or two — parties, jurisdiction, key terms. We\'ll ask follow-ups only if needed.')],
                            [__('Watch the citation gate'), __('Every clause is checked against the live legal corpus. Unverified citations are flagged before you sign anything.')],
                            [__('Export bilingual .docx'), __('Side-by-side Arabic and English in a single file, ready to send.')],
                        ] as $idx => [$title, $desc])
                            <li class="relative">
                                <span class="absolute -start-[2.2rem] flex size-6 items-center justify-center rounded-full bg-[var(--color-accent)] text-[11px] font-semibold tabular-nums text-white">{{ $idx + 1 }}</span>
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $title }}</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-zinc-500">{{ $desc }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    @endif

    {{-- KPI strip --}}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Knowledge base', $k['documents'], __(':n from official sources', ['n' => $k['official']]), false],
            ['Drafting sessions', $k['sessions'], __('All time'), false],
            ['Contracts', $k['contracts'], __(':n finalised', ['n' => $k['finalised']]), false],
            ['Spend today', '$'.number_format($this->spend['today_global_usd'], 4), __('cap $:c', ['c' => number_format($this->spend['today_global_budget_usd'], 2)]), true],
        ] as [$label, $value, $sub, $isMoney])
            <div class="bezel">
                <div class="bezel-inner p-5">
                    <span class="eyebrow-tag">{{ __($label) }}</span>
                    <p class="display mt-4 text-3xl text-zinc-900 dark:text-zinc-50 {{ $isMoney ? 'tabular-nums' : '' }}">{{ $value }}</p>
                    <p class="mt-1 text-[11px] text-zinc-500 {{ $isMoney ? 'tabular-nums' : '' }}">{{ $sub }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Sample drafts — instant-gratification demos for the first session --}}
    <div class="bezel">
        <div class="bezel-inner p-7">
            <div class="mb-5 flex items-baseline justify-between">
                <span class="eyebrow-tag">{{ __('Try a sample') }}</span>
                <span class="text-xs text-zinc-500">{{ __('Pre-populated drafts ready to inspect') }}</span>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['corporate', __('Shareholders agreement'), __('LLC, 60/40 split, EG'), 'building-office-2'],
                    ['saas', __('SaaS subscription'), __('Cloud platform, 12-month, EGP'), 'cloud'],
                    ['dpa', __('Data processing addendum'), __('PDPL-compliant, controller/processor'), 'shield-check'],
                    ['employment', __('Employment contract'), __('Full-time, Cairo, EGP 25k/mo'), 'briefcase'],
                ] as [$kind, $title, $desc, $icon])
                    <button wire:click="trySample('{{ $kind }}')" :disabled="$sampleBusy"
                        class="group relative block rounded-[0.875rem] border hairline p-4 text-start transition hover:border-[var(--color-accent)] hover:bg-[var(--color-parchment)]/40 disabled:opacity-50 disabled:cursor-wait">
                        <div class="flex items-start justify-between gap-2">
                            <flux:icon name="{{ $icon }}" class="size-5 text-zinc-400 transition group-hover:text-[var(--color-accent)]" />
                            <flux:icon.sparkles class="size-3.5 text-zinc-300 transition group-hover:text-[var(--color-accent)]" />
                        </div>
                        <p class="mt-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $title }}</p>
                        <p class="mt-1 text-[11px] text-zinc-500 leading-relaxed">{{ $desc }}</p>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Library coverage — the moat at a glance --}}
    @if (count($this->topCategories) > 0)
        <div class="bezel">
            <div class="bezel-inner p-7">
                <div class="mb-5 flex items-baseline justify-between">
                    <span class="eyebrow-tag">{{ __('Library coverage') }}</span>
                    <a href="{{ route('lawyer.knowledge') }}" wire:navigate class="text-xs text-[var(--color-accent)] hover:underline">{{ __('Browse') }} →</a>
                </div>
                @php($maxCount = max(array_column($this->topCategories, 'count')))
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($this->topCategories as $row)
                        @php($pct = $maxCount > 0 ? (int) round($row['count'] / $maxCount * 100) : 0)
                        <a href="{{ route('lawyer.knowledge') }}?cat={{ $row['category'] }}"
                           wire:navigate
                           class="group block rounded-md border hairline p-3.5 transition hover:border-[var(--color-accent)]">
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="text-[11px] font-semibold uppercase tracking-[0.1em] text-zinc-700 dark:text-zinc-300">{{ $row['category'] }}</span>
                                <span class="display text-lg text-zinc-900 dark:text-zinc-50 tabular-nums">{{ $row['count'] }}</span>
                            </div>
                            <div class="mt-2.5 h-1 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full bg-[var(--color-accent)] transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Two-column: freshness + activity --}}
    <div class="grid gap-5 lg:grid-cols-3">
        {{-- Freshness card --}}
        <div class="bezel lg:col-span-1">
            <div class="bezel-inner p-6">
                <div class="flex items-baseline justify-between">
                    <span class="eyebrow-tag">{{ __('Legal database freshness') }}</span>
                    <a href="{{ route('lawyer.knowledge') }}" wire:navigate class="text-xs text-[var(--color-accent)] hover:underline">{{ __('Manage') }} →</a>
                </div>
                @php($f = $this->freshness)
                <div class="mt-5 flex items-baseline gap-3">
                    <span class="display text-4xl text-zinc-900 dark:text-zinc-50 tabular-nums">{{ $f['pct'] }}%</span>
                    <span class="text-sm text-zinc-500">{{ __('verified within window') }}</span>
                </div>
                <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <div class="h-full rounded-full transition-all" style="width: {{ $f['pct'] }}%; background: {{ $f['pct'] >= 80 ? 'var(--color-success)' : ($f['pct'] >= 50 ? 'var(--color-warning)' : 'var(--color-danger)') }}"></div>
                </div>
                <dl class="mt-6 grid grid-cols-2 gap-x-3 gap-y-2.5 text-sm">
                    <dt class="text-zinc-500">{{ __('Up to date') }}</dt>
                    <dd class="text-end font-medium tabular-nums">{{ $f['fresh'] }}</dd>
                    <dt class="text-zinc-500">{{ __('Need re-verify') }}</dt>
                    <dd class="text-end font-medium tabular-nums">{{ $f['stale'] }}</dd>
                    <dt class="text-zinc-500">{{ __('Total documents') }}</dt>
                    <dd class="text-end font-medium tabular-nums">{{ $f['total'] }}</dd>
                </dl>
            </div>
        </div>

        {{-- Recent contracts --}}
        <div class="bezel lg:col-span-1">
            <div class="bezel-inner p-6">
                <div class="flex items-baseline justify-between">
                    <span class="eyebrow-tag">{{ __('Recent contracts') }}</span>
                    <a href="{{ route('lawyer.contracts') }}" wire:navigate class="text-xs text-[var(--color-accent)] hover:underline">{{ __('All') }} →</a>
                </div>
                @if ($this->recentContracts->isEmpty())
                    <div class="mt-5 text-center">
                        <div class="mx-auto bezel inline-block">
                            <div class="bezel-inner px-5 py-4">
                                <flux:icon.document-text class="mx-auto size-7 text-zinc-300 dark:text-zinc-700" />
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-zinc-500">{{ __('No contracts yet.') }}</p>
                        <a href="{{ route('lawyer.chat') }}" wire:navigate class="mt-1 inline-block text-sm text-[var(--color-accent)] hover:underline">{{ __('Start your first draft') }} →</a>
                    </div>
                @else
                    <ul class="mt-4 divide-y hairline">
                        @foreach ($this->recentContracts as $c)
                            @php($pillClass = $c->status === 'finalized' ? 'pill pill-success' : ($c->status === 'refused' ? 'pill pill-warning' : 'pill pill-neutral'))
                            <li class="flex items-center justify-between gap-3 py-3">
                                <a href="{{ route('lawyer.contracts') }}" wire:navigate class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $c->title }}</span>
                                    <span class="text-[11px] text-zinc-500 tabular-nums">v{{ $c->version }} · {{ $c->updated_at?->diffForHumans() }}</span>
                                </a>
                                <span class="{{ $pillClass }}">{{ $c->status }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Recent activity --}}
        <div class="bezel lg:col-span-1">
            <div class="bezel-inner p-6">
                <div class="flex items-baseline justify-between">
                    <span class="eyebrow-tag">{{ __('Activity') }}</span>
                    <a href="{{ route('lawyer.audit') }}" wire:navigate class="text-xs text-[var(--color-accent)] hover:underline">{{ __('Audit log') }} →</a>
                </div>
                @if ($this->recentActivity->isEmpty())
                    <p class="mt-6 text-sm text-zinc-500">{{ __('Nothing recent.') }}</p>
                @else
                    <ul class="mt-5 space-y-3.5">
                        @foreach ($this->recentActivity as $a)
                            <li class="flex gap-3 text-sm">
                                <span class="mt-1.5 size-1.5 flex-shrink-0 rounded-full bg-[var(--color-accent)]/40"></span>
                                <div class="min-w-0 flex-1">
                                    <span class="block truncate font-mono text-[10px] uppercase tracking-wider text-zinc-500">{{ $a->action }}</span>
                                    <span class="block text-[12px] text-zinc-700 dark:text-zinc-300">{{ \Illuminate\Support\Str::limit($a->summary ?? '—', 60) }}</span>
                                    <span class="text-[10px] text-zinc-400 tabular-nums">{{ $a->created_at?->diffForHumans() }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick start --}}
    <div class="bezel">
        <div class="bezel-inner overflow-hidden">
            <div class="border-b hairline px-7 py-4">
                <span class="eyebrow-tag">{{ __('Get started') }}</span>
            </div>
            <ol class="grid gap-3 p-6 md:grid-cols-4">
                @foreach ([
                    ['1', __('Add references'), __('Paste, upload, or pull from the legal database'), 'lawyer.knowledge'],
                    ['2', __('Pick a template'), __('Optional — speeds up routine drafts'), 'lawyer.templates'],
                    ['3', __('Draft a contract'), __('Describe the deal, AI grounds it in law'), 'lawyer.chat'],
                    ['4', __('Review & export'), __('Download .docx, finalize, share'), 'lawyer.contracts'],
                ] as [$num, $title, $desc, $route])
                    <a href="{{ route($route) }}" wire:navigate class="group block rounded-[0.875rem] border hairline p-5 transition hover:border-[var(--color-accent)] hover:bg-[var(--color-parchment)]/50">
                        <span class="font-serif text-3xl text-zinc-300 transition group-hover:text-[var(--color-accent)] dark:text-zinc-700">{{ $num }}</span>
                        <p class="mt-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $title }}</p>
                        <p class="mt-1 text-xs text-zinc-500 leading-relaxed">{{ $desc }}</p>
                    </a>
                @endforeach
            </ol>
        </div>
    </div>
</div>
