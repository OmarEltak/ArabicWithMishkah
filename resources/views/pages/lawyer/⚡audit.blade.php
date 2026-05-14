<?php

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Audit log')] class extends Component
{
    use WithPagination;

    #[Url(as: 'action')]
    public string $filterAction = '';

    #[Url(as: 'q')]
    public string $filterQ = '';

    #[Computed]
    public function rows()
    {
        // Scope to the current user. System rows (user_id null) are included
        // so the lawyer sees corpus-ingestion / freshness events that happened
        // on documents in their workspace, but never another user's audit
        // trail. This route is in the auth+verified group with no admin
        // gate, so per-user scoping is mandatory.
        $q = AuditLog::query()
            ->with(['user', 'subject'])
            ->where(function ($q): void {
                $q->where('user_id', Auth::id())->orWhereNull('user_id');
            })
            ->latest('id');
        if ($this->filterAction !== '') {
            $q->where('action', 'like', $this->filterAction.'%');
        }
        if ($this->filterQ !== '') {
            $q->where('summary', 'like', '%'.$this->filterQ.'%');
        }

        return $q->paginate(50);
    }

    #[Computed]
    public function actions(): array
    {
        return AuditLog::query()
            ->where(function ($q): void {
                $q->where('user_id', Auth::id())->orWhereNull('user_id');
            })
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->all();
    }
}; ?>

<div class="mx-auto w-full max-w-7xl px-6 py-8">
    {{-- Header --}}
    <div class="border-b hairline pb-8">
        <span class="eyebrow-tag">{{ __('Compliance') }}</span>
        <h1 class="display mt-3 text-3xl text-zinc-900 dark:text-zinc-50">{{ __('Audit log') }}</h1>
        <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
            {{ __('Append-only history of every contract, document, and session event. Used for legal review and compliance audits.') }}
        </p>
    </div>

    {{-- Filters --}}
    <div class="mt-6 flex flex-wrap items-end gap-4 bezel">
        <div class="bezel-inner flex flex-wrap items-end gap-4 p-4 w-full">
        <flux:field class="min-w-[12rem]">
            <flux:label>{{ __('Action') }}</flux:label>
            <flux:select wire:model.live="filterAction">
                <flux:select.option value="">{{ __('— All —') }}</flux:select.option>
                @foreach ($this->actions as $a)
                    <flux:select.option value="{{ $a }}">{{ $a }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:field>
        <flux:field class="flex-1 min-w-[16rem]">
            <flux:label>{{ __('Search summary') }}</flux:label>
            <flux:input wire:model.live.debounce.500ms="filterQ" icon="magnifying-glass" placeholder="{{ __('search…') }}" />
        </flux:field>
        </div>
    </div>

    {{-- Table --}}
    <div class="mt-6 bezel">
        <div class="bezel-inner overflow-hidden">
        <table class="w-full text-sm">
            <thead class="border-b hairline bg-zinc-50/50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-950/30">
                <tr>
                    <th class="px-4 py-3 text-start font-medium">{{ __('When') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Who') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Action') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Subject') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ __('Summary') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->rows as $r)
                    <tr class="border-t hairline align-top transition hover:bg-[var(--color-paper-edge)]/40 dark:hover:bg-zinc-950/30">
                        <td class="px-4 py-3 text-xs text-zinc-500" title="{{ $r->created_at }}">{{ $r->created_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-xs">
                            @if ($r->user)
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $r->user->name }}</span>
                            @else
                                <span class="pill pill-neutral">{{ __('system') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3"><span class="font-mono text-[11px] text-zinc-700 dark:text-zinc-300">{{ $r->action }}</span></td>
                        <td class="px-4 py-3 text-xs text-zinc-600 dark:text-zinc-400">
                            @php
                                $subjectUrl = match ($r->subject_type) {
                                    \App\Models\Contract::class => $r->subject_id ? route('lawyer.contracts', ['contract' => $r->subject_id]) : null,
                                    \App\Models\LegalDocument::class => $r->subject_id ? route('lawyer.law-show', ['docId' => $r->subject_id]) : null,
                                    \App\Models\ContractTemplate::class => route('lawyer.templates'),
                                    default => null,
                                };
                            @endphp
                            @if ($r->subject_type && $subjectUrl)
                                <a href="{{ $subjectUrl }}" wire:navigate class="hover:underline">
                                    {{ class_basename($r->subject_type) }} <span class="font-mono text-zinc-400">#{{ $r->subject_id }}</span>
                                </a>
                            @elseif ($r->subject_type)
                                {{ class_basename($r->subject_type) }} <span class="font-mono text-zinc-400">#{{ $r->subject_id }}</span>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-zinc-700 dark:text-zinc-300">{{ $r->summary ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-12 text-center text-sm text-zinc-500">{{ __('No audit entries match.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">{{ $this->rows->links() }}</div>
</div>
