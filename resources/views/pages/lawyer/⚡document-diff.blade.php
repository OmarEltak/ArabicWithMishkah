<?php

use App\Models\LegalDocument;
use App\Services\AI\DiffService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Document version diff')] class extends Component {
    public int $docId = 0;
    public ?int $left = null;
    public ?int $right = null;

    public function mount(int $docId): void
    {
        $this->docId = $docId;
    }

    #[Computed]
    public function document(): ?LegalDocument
    {
        return LegalDocument::find($this->docId);
    }

    #[Computed]
    public function diff(): array
    {
        $doc = $this->document;
        if (! $doc) {
            return ['versions' => [], 'left' => 0, 'right' => 0, 'rows' => []];
        }

        return app(DiffService::class)->compare($doc, $this->left, $this->right);
    }

    public function setLeft(int $v): void { $this->left = $v; }
    public function setRight(int $v): void { $this->right = $v; }
}; ?>

<div class="flex flex-col gap-4 p-6">
    @if (! $this->document)
        <flux:text>{{ __('Document not found.') }}</flux:text>
    @else
        @php($d = $this->diff)
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ $this->document->title }}</flux:heading>
                <flux:subheading>{{ __('Version history & diff') }}</flux:subheading>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <div>
                    <span class="text-zinc-500">{{ __('Compare') }}</span>
                    @foreach ($d['versions'] as $v)
                        <button wire:click="setLeft({{ $v }})"
                            class="ml-1 rounded-md border px-2 py-0.5 text-xs
                            {{ $d['left'] === $v ? 'border-blue-600 bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'border-zinc-300 text-zinc-600 dark:border-zinc-700 dark:text-zinc-300' }}">v{{ $v }}</button>
                    @endforeach
                </div>
                <span class="text-zinc-400">→</span>
                <div>
                    @foreach ($d['versions'] as $v)
                        <button wire:click="setRight({{ $v }})"
                            class="ml-1 rounded-md border px-2 py-0.5 text-xs
                            {{ $d['right'] === $v ? 'border-emerald-600 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'border-zinc-300 text-zinc-600 dark:border-zinc-700 dark:text-zinc-300' }}">v{{ $v }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        @if (count($d['versions']) <= 1)
            <flux:text class="text-zinc-500">{{ __('Only one version exists for this document. Diff appears once an upstream amendment is detected.') }}</flux:text>
        @else
            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-3 py-2 text-start w-12">#</th>
                            <th class="px-3 py-2 text-start">{{ __('v:n', ['n' => $d['left']]) }}</th>
                            <th class="px-3 py-2 text-start">{{ __('v:n', ['n' => $d['right']]) }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($d['rows'] as $row)
                            @php(
                                $rowClass = match ($row['status']) {
                                    'added' => 'bg-emerald-50 dark:bg-emerald-900/20',
                                    'removed' => 'bg-rose-50 dark:bg-rose-900/20',
                                    'changed' => 'bg-amber-50 dark:bg-amber-900/20',
                                    default => '',
                                }
                            )
                            <tr class="border-t border-zinc-200 align-top dark:border-zinc-700 {{ $rowClass }}">
                                <td class="px-3 py-2 text-xs text-zinc-500">{{ $row['position'] }}</td>
                                <td class="px-3 py-2 text-xs leading-relaxed">{!! $row['left'] !== null ? e($row['left']) : '<span class="text-rose-600">—</span>' !!}</td>
                                <td class="px-3 py-2 text-xs leading-relaxed">{!! $row['right'] !== null ? e($row['right']) : '<span class="text-zinc-400">—</span>' !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>
