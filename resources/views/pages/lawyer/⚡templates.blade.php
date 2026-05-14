<?php

use App\Models\ContractTemplate;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('Templates')] class extends Component
{
    public ?int $editId = null;

    #[Validate('required|string|min:2|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:64')]
    public string $category = '';

    #[Validate('nullable|string|max:64')]
    public string $jurisdiction = '';

    #[Validate('nullable|string|max:8')]
    public string $language = 'en';

    #[Validate('nullable|string|max:1000')]
    public string $description = '';

    #[Validate('required|string|min:20')]
    public string $body = '';

    /**
     * Structured field list. Each row: ['label' => 'اسم البائع', 'key' => 'seller_name', 'type' => 'text'].
     * The "key" is what gets used in the body as {{key}}; "label" is what the attorney
     * sees throughout the UI. Auto-derived from label via slug if blank.
     *
     * @var array<int, array{label:string, key:string, type:string}>
     */
    public array $fields = [];

    /** Available controlled vocabularies — instead of free-text developer entry. */
    public const CATEGORIES = [
        'nda' => 'Non-Disclosure Agreement',
        'employment' => 'Employment',
        'services' => 'Services / Consulting',
        'real-estate' => 'Real Estate / Sale',
        'lease' => 'Lease / Rental',
        'power-of-attorney' => 'Power of Attorney',
        'partnership' => 'Partnership',
        'sale-of-goods' => 'Sale of Goods',
        'other' => 'Other',
    ];

    public const JURISDICTIONS = [
        'EG' => 'Egypt',
        'SA' => 'Saudi Arabia',
        'AE' => 'United Arab Emirates',
        'KW' => 'Kuwait',
        'QA' => 'Qatar',
        'BH' => 'Bahrain',
        'OM' => 'Oman',
        'JO' => 'Jordan',
        'LB' => 'Lebanon',
        'US' => 'United States',
        'UK' => 'United Kingdom',
        'OTHER' => 'Other',
    ];

    public const LANGUAGES = [
        'ar' => 'العربية',
        'en' => 'English',
    ];

    public const FIELD_TYPES = [
        'text' => 'Text',
        'number' => 'Number / Amount',
        'date' => 'Date',
    ];

    #[Computed]
    public function templates()
    {
        return ContractTemplate::query()
            ->where(function ($q) {
                $q->where('user_id', Auth::id())->orWhere('is_system', true);
            })
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();
    }

    public function startNew(): void
    {
        $this->reset(['editId', 'name', 'category', 'jurisdiction', 'description', 'body', 'fields']);
        $this->language = 'en';
    }

    public function edit(int $id): void
    {
        // Scope the read: a user can edit only their own templates, plus
        // surface system templates (they're read-only — warn). Reading a
        // template owned by ANOTHER user must 404, otherwise the form
        // hydrates with that user's template body (information disclosure).
        $t = ContractTemplate::query()
            ->where('id', $id)
            ->where(function ($q): void {
                $q->where('user_id', Auth::id())->orWhere('is_system', true);
            })
            ->first();
        if (! $t) {
            return;
        }
        if ($t->is_system && $t->user_id !== Auth::id()) {
            Flux::toast(variant: 'warning', text: __('System templates are read-only. Duplicate to customise.'));

            return;
        }
        $this->editId = $t->id;
        $this->name = $t->name;
        $this->category = $t->category ?? '';
        $this->jurisdiction = $t->jurisdiction ?? '';
        $this->language = $t->language ?? 'en';
        $this->description = $t->description ?? '';
        $this->body = $t->body;

        // Hydrate the structured fields list from required_fields,
        // back-filling labels from key names where missing.
        $stored = is_array($t->required_fields) ? $t->required_fields : [];
        $this->fields = collect($stored)
            ->map(fn ($type, $key) => [
                'key' => (string) $key,
                'label' => self::humaniseKey((string) $key),
                'type' => is_string($type) ? $type : 'text',
            ])
            ->values()
            ->all();
    }

    public function duplicate(int $id): void
    {
        // Same scope as edit() — you can duplicate your own templates plus
        // any system template, but never another user's private template.
        $t = ContractTemplate::query()
            ->where('id', $id)
            ->where(function ($q): void {
                $q->where('user_id', Auth::id())->orWhere('is_system', true);
            })
            ->first();
        if (! $t) {
            return;
        }
        $new = $t->replicate();
        $new->user_id = Auth::id();
        $new->is_system = false;
        $new->name = $t->name.' (copy)';
        $new->slug = Str::slug($new->name).'-'.Str::random(6);
        $new->save();
        Flux::toast(variant: 'success', text: __('Template duplicated.'));
        $this->edit($new->id);
        unset($this->templates);
    }

    /**
     * Add a new field row from the form-side "+ Add field" button.
     * Inserts a blank row; the attorney fills label + type, key auto-derives.
     */
    public function addField(): void
    {
        $this->fields[] = ['label' => '', 'key' => '', 'type' => 'text'];
    }

    public function removeField(int $index): void
    {
        unset($this->fields[$index]);
        $this->fields = array_values($this->fields);
    }

    /**
     * Scan the body for any `{{xxx}}` placeholders not yet present in the
     * fields list and add them with a humanised default label. Triggered by
     * a button so the attorney sees what's about to happen.
     */
    public function syncFieldsFromBody(): void
    {
        if (! preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/u', $this->body, $matches)) {
            Flux::toast(variant: 'info', text: __('No {{placeholders}} found in the body.'));

            return;
        }
        $existingKeys = array_column($this->fields, 'key');
        $added = 0;
        foreach (array_unique($matches[1]) as $key) {
            if (! in_array($key, $existingKeys, true)) {
                $this->fields[] = [
                    'key' => $key,
                    'label' => self::humaniseKey($key),
                    'type' => str_contains($key, 'date') ? 'date'
                        : (str_contains($key, 'price') || str_contains($key, 'amount') || str_contains($key, '_egp') || str_contains($key, 'salary') || str_contains($key, 'fee') ? 'number'
                        : 'text'),
                ];
                $added++;
            }
        }
        if ($added > 0) {
            Flux::toast(variant: 'success', text: __(':n field(s) added from body.', ['n' => $added]));
        } else {
            Flux::toast(variant: 'info', text: __('All placeholders are already in the field list.'));
        }
    }

    /**
     * Live-update the auto-derived key when the attorney edits a field's
     * label, so they don't have to think about the snake_case identifier.
     * Doesn't overwrite a key the user has manually edited (heuristic:
     * if key matches the slug of the previous label, treat it as auto).
     */
    public function updatedFields($value, $name): void
    {
        if (preg_match('/^fields\.(\d+)\.label$/', $name, $m)) {
            $i = (int) $m[1];
            $label = trim((string) $value);
            if ($label === '') {
                return;
            }
            $current = $this->fields[$i]['key'] ?? '';
            // Only auto-update if the existing key is empty or looks auto-derived.
            if ($current === '' || $current === Str::slug($this->fields[$i]['label'] ?? '', '_')) {
                $this->fields[$i]['key'] = self::deriveKey($label);
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        // Normalise fields: drop empty rows, ensure keys, dedupe.
        $required = [];
        $seen = [];
        foreach ($this->fields as $f) {
            $label = trim((string) ($f['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $key = trim((string) ($f['key'] ?? '')) ?: self::deriveKey($label);
            if (in_array($key, $seen, true)) {
                continue;
            }
            $seen[] = $key;
            $type = in_array(($f['type'] ?? 'text'), array_keys(self::FIELD_TYPES), true) ? $f['type'] : 'text';
            $required[$key] = $type;
        }

        $payload = [
            'user_id' => Auth::id(),
            'name' => $this->name,
            'category' => $this->category ?: null,
            'jurisdiction' => $this->jurisdiction ?: null,
            'language' => $this->language ?: 'en',
            'description' => $this->description ?: null,
            'body' => $this->body,
            'required_fields' => $required,
            'is_system' => false,
        ];

        if ($this->editId) {
            $t = ContractTemplate::query()->where('id', $this->editId)->where('user_id', Auth::id())->first();
            if ($t) {
                $t->update($payload);
                Flux::toast(variant: 'success', text: __('Template updated.'));
            }
        } else {
            $payload['slug'] = Str::slug($this->name).'-'.Str::random(6);
            ContractTemplate::create($payload);
            Flux::toast(variant: 'success', text: __('Template created.'));
            $this->startNew();
        }
        unset($this->templates);
    }

    public function delete(int $id): void
    {
        $t = ContractTemplate::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->where('is_system', false)
            ->first();
        if ($t) {
            $t->delete();
            $this->startNew();
            unset($this->templates);
            Flux::toast(variant: 'success', text: __('Deleted.'));
        }
    }

    /** snake_case identifier from a free-text label, ASCII-safe across Arabic. */
    private static function deriveKey(string $label): string
    {
        $slug = Str::slug($label, '_');
        if ($slug === '') {
            // Pure-Arabic label: hash to a stable key fragment.
            $slug = 'field_'.substr(md5($label), 0, 8);
        }

        return $slug;
    }

    /** "seller_name" → "Seller name". Falls back to the key itself for non-ASCII. */
    private static function humaniseKey(string $key): string
    {
        return Str::of($key)->replace('_', ' ')->title()->__toString();
    }
}; ?>

<div class="mx-auto flex h-[calc(100vh-1rem)] w-full max-w-screen-2xl gap-4 p-4"
     x-data="{ drawerOpen: false }"
     @keydown.escape.window="drawerOpen = false">

    {{-- Mobile drawer toggle — visible only below lg --}}
    <div class="fixed start-3 top-20 z-30 lg:hidden">
        <button type="button"
            @click="drawerOpen = true"
            class="flex items-center gap-2 rounded-full border hairline bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 shadow-sm dark:bg-zinc-900 dark:text-zinc-200">
            <flux:icon.queue-list class="size-4" />
            {{ __('Templates') }}
        </button>
    </div>

    {{-- Backdrop --}}
    <div class="fixed inset-0 z-40 bg-black/40 lg:hidden"
         x-show="drawerOpen"
         x-transition.opacity
         @click="drawerOpen = false"
         x-cloak></div>

    {{-- Templates list — drawer on mobile, fixed column on lg+ --}}
    <aside class="fixed inset-y-0 start-0 z-50 w-72 flex-shrink-0 transform bg-white shadow-2xl transition-transform duration-200 dark:bg-zinc-900
                  lg:static lg:z-auto lg:flex lg:translate-x-0 lg:shadow-none lg:bg-transparent lg:dark:bg-transparent lg:bezel"
           :class="drawerOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           x-cloak>
        <div class="bezel-inner flex w-full flex-col overflow-hidden">
        <div class="flex items-center justify-between border-b hairline px-4 py-3">
            <span class="eyebrow-tag">{{ __('Templates') }}</span>
            <div class="flex items-center gap-1">
                <flux:button size="xs" wire:click="startNew" icon="plus">{{ __('New') }}</flux:button>
                <button type="button" @click="drawerOpen = false"
                    class="rounded-md p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 lg:hidden dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
                    aria-label="{{ __('Close') }}">
                    <flux:icon.x-mark class="size-4" />
                </button>
            </div>
        </div>
        <ul class="flex-1 overflow-y-auto p-2 space-y-1">
            @forelse ($this->templates as $t)
                <li class="group flex items-center gap-1">
                    <button wire:click="edit({{ $t->id }})"
                        @click="drawerOpen = false"
                        class="flex-1 truncate rounded-md px-3 py-2 text-start text-sm transition
                        {{ $editId === $t->id ? 'bg-[var(--color-parchment)] dark:bg-zinc-800' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800/50' }}">
                        <span class="block truncate font-medium text-zinc-900 dark:text-zinc-100">{{ $t->name }}</span>
                        <span class="text-[10px] text-zinc-500">
                            {{ $t->category ?? __('uncategorised') }}
                            @if ($t->is_system) · <span class="pill pill-neutral !py-0 !px-1.5 !text-[9px]">system</span> @endif
                        </span>
                    </button>
                    <button wire:click="duplicate({{ $t->id }})"
                        title="{{ __('Duplicate') }}"
                        class="rounded-md p-1.5 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                        <flux:icon.document-duplicate class="size-4" />
                    </button>
                </li>
            @empty
                <li class="p-4 text-center text-xs text-zinc-500">{{ __('No templates yet.') }}</li>
            @endforelse
        </ul>
        </div>
    </aside>

    {{-- Form --}}
    <main class="flex-1 bezel">
        <div class="bezel-inner overflow-y-auto h-full">
        <form wire:submit="save" class="space-y-8 p-6 pb-12">

            {{-- Header --}}
            <div class="border-b hairline pb-6">
                <span class="eyebrow-tag">{{ $editId ? __('Edit template') : __('New template') }}</span>
                <input
                    wire:model="name"
                    type="text"
                    placeholder="{{ __('e.g. Egyptian land sale contract') }}"
                    class="display mt-2 w-full border-0 bg-transparent p-0 text-3xl text-zinc-900 outline-none placeholder:text-zinc-400 focus:ring-0 dark:text-zinc-50"
                    required />
                <input
                    wire:model="description"
                    type="text"
                    placeholder="{{ __('One-line description — what this template is for') }}"
                    class="mt-1 w-full border-0 bg-transparent p-0 text-sm text-zinc-600 outline-none placeholder:text-zinc-400 focus:ring-0 dark:text-zinc-400" />
                @error('name') <flux:text variant="danger" class="mt-2">{{ $message }}</flux:text> @enderror
            </div>

            {{-- Metadata row --}}
            <div class="grid gap-4 md:grid-cols-3">
                <flux:field>
                    <flux:label>{{ __('Category') }}</flux:label>
                    <flux:select wire:model="category">
                        <flux:select.option value="">{{ __('— Select —') }}</flux:select.option>
                        @foreach ($this::CATEGORIES as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Jurisdiction') }}</flux:label>
                    <flux:select wire:model="jurisdiction">
                        <flux:select.option value="">{{ __('— Select —') }}</flux:select.option>
                        @foreach ($this::JURISDICTIONS as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Language') }}</flux:label>
                    <flux:select wire:model="language">
                        @foreach ($this::LANGUAGES as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            {{-- Fields builder --}}
            <div>
                <div class="mb-2 flex items-end justify-between gap-3">
                    <div>
                        <span class="eyebrow-tag">{{ __('Information you collect') }}</span>
                        <flux:text class="mt-1 text-xs text-zinc-500">{{ __('Each row becomes a question the assistant asks (or extracts from the user\'s description). Use the field key inside the contract body wherever you want that value inserted.') }}</flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:button type="button" size="sm" variant="ghost" wire:click="syncFieldsFromBody" icon="sparkles">
                            {{ __('Detect from body') }}
                        </flux:button>
                        <flux:button type="button" size="sm" wire:click="addField" icon="plus">
                            {{ __('Add field') }}
                        </flux:button>
                    </div>
                </div>

                @if (count($fields) === 0)
                    <div class="rounded-md border border-dashed hairline p-8 text-center">
                        <p class="text-sm text-zinc-500">{{ __('No fields yet.') }}</p>
                        <p class="mt-1 text-xs text-zinc-400">{!! __('Click "Add field" or write <code>{{placeholders}}</code> in the body and use "Detect from body".') !!}</p>
                    </div>
                @else
                    <div class="overflow-hidden rounded-lg border hairline">
                        <table class="w-full text-sm">
                            <thead class="border-b hairline bg-zinc-50/50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-950/30">
                                <tr>
                                    <th class="px-3 py-2 text-start font-medium">{{ __('Label') }}</th>
                                    <th class="px-3 py-2 text-start font-medium">{{ __('Type') }}</th>
                                    <th class="px-3 py-2 text-start font-medium">{{ __('Body marker') }}</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fields as $i => $f)
                                    @php($_marker = '{{'.($f['key'] ?? '').'}}')
                                    @php($_displayMarker = '{{'.($f['key'] ?? '—').'}}')
                                    <tr class="border-t hairline" wire:key="field-{{ $i }}">
                                        <td class="px-3 py-2">
                                            <input
                                                type="text"
                                                wire:model.live.debounce.400ms="fields.{{ $i }}.label"
                                                placeholder="{{ __('e.g. Seller\'s full name') }}"
                                                class="w-full rounded-md border hairline bg-white px-2 py-1 text-sm dark:bg-zinc-900" />
                                        </td>
                                        <td class="px-3 py-2">
                                            <select wire:model="fields.{{ $i }}.type" class="rounded-md border hairline bg-white px-2 py-1 text-sm dark:bg-zinc-900">
                                                @foreach ($this::FIELD_TYPES as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 font-mono text-xs text-zinc-500">
                                            <button type="button"
                                                onclick="navigator.clipboard.writeText(@js($_marker)); this.dataset.copied=1; setTimeout(()=>delete this.dataset.copied, 1500);"
                                                class="rounded px-1.5 py-0.5 hover:bg-zinc-100 dark:hover:bg-zinc-800"
                                                title="{{ __('Click to copy — paste this into the body where the value should appear') }}">
                                                {{ $_displayMarker }}
                                            </button>
                                        </td>
                                        <td class="px-3 py-2 text-end">
                                            <button type="button" wire:click="removeField({{ $i }})"
                                                class="rounded p-1.5 text-zinc-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-900/30">
                                                <flux:icon.x-mark class="size-4" />
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Body editor --}}
            <div>
                <div class="mb-2 flex items-end justify-between gap-3">
                    <div>
                        <span class="eyebrow-tag">{{ __('Contract body') }}</span>
                        <flux:text class="mt-1 text-xs text-zinc-500">
                            {{ __('Write the contract as you would normally. Where a value should be inserted, click a field above to copy its marker, then paste it here. Example: ') }}
                            @php($_egMarker = '{{seller_name}}')
                            <code class="rounded bg-zinc-100 px-1 font-mono text-[11px] dark:bg-zinc-800">{{ $_egMarker }}</code>.
                        </flux:text>
                    </div>
                </div>
                <textarea
                    wire:model.live.debounce.500ms="body"
                    rows="20"
                    required
                    class="block w-full rounded-md border hairline bg-white px-4 py-3 font-mono text-[13px] leading-7 text-zinc-900 outline-none focus:border-[var(--color-accent)] dark:bg-zinc-900 dark:text-zinc-100"
                    placeholder="{{ __('Start typing the contract...') }}"
                    @if ($language === 'ar') dir="rtl" @endif></textarea>
                @error('body') <flux:text variant="danger" class="mt-2">{{ $message }}</flux:text> @enderror
            </div>

            {{-- Save bar --}}
            <div class="sticky bottom-0 -mx-6 flex items-center justify-between gap-3 border-t hairline bg-white px-6 py-3 dark:bg-zinc-900">
                @if ($editId)
                    <button type="button" wire:click="delete({{ $editId }})" wire:confirm="{{ __('Delete this template?') }}"
                        class="text-sm text-[var(--color-danger)] hover:underline">
                        {{ __('Delete template') }}
                    </button>
                @else
                    <span></span>
                @endif
                <div class="flex gap-2">
                    <flux:button type="button" variant="ghost" wire:click="startNew">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">{{ $editId ? __('Save changes') : __('Create template') }}</flux:button>
                </div>
            </div>
        </form>
        </div>
    </main>
</div>
