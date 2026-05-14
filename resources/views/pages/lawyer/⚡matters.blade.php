<?php

use App\Models\Matter;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Matters')] class extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $clientName = '';

    public string $matterName = '';

    public string $reference = '';

    public string $notes = '';

    public string $status = 'active';

    public string $filter = '';

    public string $filterStatus = 'active'; // 'active' | 'archived' | 'all'

    #[Computed]
    public function matters()
    {
        $q = Matter::query()
            ->where('user_id', Auth::id())
            ->withCount('contracts', 'chatSessions')
            ->latest('updated_at');

        if ($this->filterStatus !== 'all') {
            $q->where('status', $this->filterStatus);
        }

        if (trim($this->filter) !== '') {
            $needle = trim($this->filter);
            $q->where(function ($qq) use ($needle): void {
                $qq->where('client_name', 'like', '%'.$needle.'%')
                    ->orWhere('matter_name', 'like', '%'.$needle.'%')
                    ->orWhere('reference', 'like', '%'.$needle.'%');
            });
        }

        return $q->paginate(20);
    }

    public function startCreate(): void
    {
        $this->reset(['editingId', 'clientName', 'matterName', 'reference', 'notes']);
        $this->status = 'active';
        $this->editingId = 0; // sentinel meaning "open create form, no record yet"
    }

    public function startEdit(int $id): void
    {
        $m = Matter::query()->where('id', $id)->where('user_id', Auth::id())->first();
        if (! $m) {
            return;
        }
        $this->editingId = $m->id;
        $this->clientName = $m->client_name;
        $this->matterName = $m->matter_name;
        $this->reference = (string) ($m->reference ?? '');
        $this->notes = (string) ($m->notes ?? '');
        $this->status = $m->status;
    }

    public function save(): void
    {
        $this->validate([
            'clientName' => 'required|string|max:120',
            'matterName' => 'required|string|max:160',
            'reference' => 'nullable|string|max:60',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|in:active,archived',
        ]);

        $payload = [
            'user_id' => Auth::id(),
            'client_name' => trim($this->clientName),
            'matter_name' => trim($this->matterName),
            'reference' => trim($this->reference) === '' ? null : trim($this->reference),
            'notes' => trim($this->notes) === '' ? null : $this->notes,
            'status' => $this->status,
        ];

        if ($this->editingId && $this->editingId > 0) {
            $m = Matter::query()->where('id', $this->editingId)->where('user_id', Auth::id())->first();
            if ($m) {
                $m->update($payload);
                Flux::toast(variant: 'success', text: __('Matter updated.'));
            }
        } else {
            Matter::create($payload);
            Flux::toast(variant: 'success', text: __('Matter created.'));
        }

        $this->reset(['editingId', 'clientName', 'matterName', 'reference', 'notes', 'status']);
        unset($this->matters);
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'clientName', 'matterName', 'reference', 'notes', 'status']);
    }

    public function archive(int $id): void
    {
        $m = Matter::query()->where('id', $id)->where('user_id', Auth::id())->first();
        if (! $m) {
            return;
        }
        $m->update(['status' => 'archived']);
        unset($this->matters);
        Flux::toast(variant: 'success', text: __('Matter archived.'));
    }

    public function reactivate(int $id): void
    {
        $m = Matter::query()->where('id', $id)->where('user_id', Auth::id())->first();
        if (! $m) {
            return;
        }
        $m->update(['status' => 'active']);
        unset($this->matters);
        Flux::toast(variant: 'success', text: __('Matter reactivated.'));
    }
}; ?>

<div class="mx-auto w-full max-w-6xl px-6 py-8">

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4 border-b hairline pb-6">
        <div class="min-w-0">
            <span class="eyebrow-tag">{{ __('Practice management') }}</span>
            <h1 class="display mt-2 text-3xl text-zinc-900 dark:text-zinc-50">{{ __('Matters') }}</h1>
            <p class="mt-1 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Group contracts and chat sessions by client and matter. Assignments are optional — work without a matter stays in "Unassigned" forever.') }}
            </p>
        </div>
        <flux:button wire:click="startCreate" variant="primary" icon="plus">{{ __('New matter') }}</flux:button>
    </div>

    {{-- Create / edit drawer (inline panel) --}}
    @if ($editingId !== null)
        <div class="card mt-6 p-5">
            <div class="flex items-baseline justify-between">
                <span class="eyebrow-tag">{{ $editingId && $editingId > 0 ? __('Edit matter') : __('New matter') }}</span>
            </div>
            <form wire:submit="save" class="mt-4 grid gap-4 md:grid-cols-2">
                <flux:input wire:model="clientName" :label="__('Client name')" required placeholder="{{ __('e.g. Acme Inc.') }}" />
                <flux:input wire:model="matterName" :label="__('Matter')" required placeholder="{{ __('e.g. Q2 vendor onboarding') }}" />
                <flux:input wire:model="reference" :label="__('Internal reference (optional)')" placeholder="ACME-2026-01" />
                <flux:field>
                    <flux:label>{{ __('Status') }}</flux:label>
                    <flux:select wire:model="status">
                        <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                        <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                    </flux:select>
                </flux:field>
                <div class="md:col-span-2">
                    <flux:textarea wire:model="notes" :label="__('Notes (optional)')" rows="3" placeholder="{{ __('Scope, key contacts, deadlines, etc.') }}" />
                </div>
                <div class="md:col-span-2 flex gap-2">
                    <flux:button type="submit" variant="primary">{{ $editingId && $editingId > 0 ? __('Save changes') : __('Create matter') }}</flux:button>
                    <flux:button type="button" variant="ghost" wire:click="cancel">{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </div>
    @endif

    {{-- Filters --}}
    <div class="mt-6 flex flex-wrap items-end gap-3">
        <flux:input wire:model.live.debounce.300ms="filter" icon="magnifying-glass" placeholder="{{ __('Filter by client, matter, or reference…') }}" class="flex-1 min-w-[14rem]" />
        <flux:field>
            <flux:label>{{ __('Status') }}</flux:label>
            <flux:select wire:model.live="filterStatus">
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="archived">{{ __('Archived') }}</flux:select.option>
                <flux:select.option value="all">{{ __('All') }}</flux:select.option>
            </flux:select>
        </flux:field>
    </div>

    {{-- List --}}
    <div class="mt-6 space-y-3">
        @forelse ($this->matters as $m)
            <div class="card p-5" wire:key="matter-{{ $m->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-baseline gap-2">
                            <h3 class="font-serif text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $m->client_name }}</h3>
                            <span class="text-zinc-400">·</span>
                            <span class="text-zinc-700 dark:text-zinc-300">{{ $m->matter_name }}</span>
                            @if ($m->status === 'archived')
                                <span class="pill pill-neutral">{{ __('archived') }}</span>
                            @endif
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-zinc-500">
                            @if ($m->reference)
                                <span class="font-mono">{{ $m->reference }}</span>
                            @endif
                            <a href="{{ route('lawyer.contracts') }}?matter={{ $m->id }}" wire:navigate class="hover:underline">
                                {{ trans_choice('{0} no contracts|{1} 1 contract|[2,*] :count contracts', $m->contracts_count, ['count' => $m->contracts_count]) }}
                            </a>
                            <span>·</span>
                            <span>{{ trans_choice('{0} no sessions|{1} 1 session|[2,*] :count sessions', $m->chat_sessions_count, ['count' => $m->chat_sessions_count]) }}</span>
                            <span>·</span>
                            <span>{{ __('Updated :when', ['when' => $m->updated_at?->diffForHumans()]) }}</span>
                        </div>
                        @if ($m->notes)
                            <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">{{ \Illuminate\Support\Str::limit($m->notes, 200) }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-1">
                        <flux:button size="xs" variant="ghost" wire:click="startEdit({{ $m->id }})" icon="pencil-square">{{ __('Edit') }}</flux:button>
                        @if ($m->status === 'active')
                            <flux:button size="xs" variant="ghost" wire:click="archive({{ $m->id }})" icon="archive-box" title="{{ __('Archive') }}"></flux:button>
                        @else
                            <flux:button size="xs" variant="ghost" wire:click="reactivate({{ $m->id }})" icon="arrow-uturn-up" title="{{ __('Reactivate') }}"></flux:button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-md border border-dashed hairline p-12 text-center">
                <flux:icon.folder class="mx-auto size-10 text-zinc-300 dark:text-zinc-700" />
                <h2 class="display mt-4 text-lg text-zinc-900 dark:text-zinc-50">{{ __('No matters yet') }}</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-zinc-500">
                    {{ __('Create your first matter to group contracts and chat sessions by client. Existing work stays unassigned unless you move it.') }}
                </p>
                <div class="mt-5">
                    <flux:button wire:click="startCreate" variant="primary" icon="plus">{{ __('Create the first matter') }}</flux:button>
                </div>
            </div>
        @endforelse
    </div>

    @if ($this->matters->hasPages())
        <div class="mt-6">{{ $this->matters->onEachSide(1)->links() }}</div>
    @endif
</div>
