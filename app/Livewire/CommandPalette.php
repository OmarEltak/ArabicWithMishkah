<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\LegalClipping;
use App\Models\LegalDocument;
use App\Models\Matter;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Global command palette (⌘K / Ctrl+K). Triggered from any authenticated
 * page; provides fuzzy-search across the lawyer's own data plus the public
 * corpus, and a handful of quick-action commands ("new draft", "new matter").
 *
 * Results are scoped per-user; no chance of cross-tenant leakage.
 * Query runs on every keystroke (live, debounced 200ms client-side).
 */
class CommandPalette extends Component
{
    public bool $open = false;

    public string $q = '';

    /** Action commands always present; matched against $q. */
    private const ACTIONS = [
        ['label_en' => 'New draft', 'label_ar' => 'مسودة جديدة', 'route' => 'lawyer.chat', 'icon' => '✏'],
        ['label_en' => 'New matter', 'label_ar' => 'موضوع جديد', 'route' => 'lawyer.matters', 'icon' => '📁'],
        ['label_en' => 'Search laws', 'label_ar' => 'بحث في القوانين', 'route' => 'lawyer.law-search', 'icon' => '⚖'],
        ['label_en' => 'Clippings', 'label_ar' => 'المقتطفات', 'route' => 'lawyer.clippings', 'icon' => '🔖'],
        ['label_en' => 'Knowledge base', 'label_ar' => 'مكتبة القوانين', 'route' => 'lawyer.knowledge', 'icon' => '📚'],
        ['label_en' => 'Templates', 'label_ar' => 'القوالب', 'route' => 'lawyer.templates', 'icon' => '🗎'],
        ['label_en' => 'Usage & cost', 'label_ar' => 'الاستخدام والتكلفة', 'route' => 'lawyer.usage', 'icon' => '💲'],
        ['label_en' => 'Audit log', 'label_ar' => 'سجل التدقيق', 'route' => 'lawyer.audit', 'icon' => '🛡'],
        ['label_en' => 'Settings', 'label_ar' => 'الإعدادات', 'route' => 'profile.edit', 'icon' => '⚙'],
    ];

    #[On('open-command-palette')]
    public function show(): void
    {
        $this->open = true;
        $this->q = '';
    }

    public function close(): void
    {
        $this->open = false;
        $this->q = '';
    }

    /**
     * @return array<int, array{kind:string, label:string, sub:?string, url:string}>
     */
    public function getResultsProperty(): array
    {
        $needle = trim($this->q);
        $isAr = app()->getLocale() === 'ar';
        $userId = Auth::id();

        // Empty query: show only actions.
        if ($needle === '') {
            return $this->actionResults($isAr);
        }

        $out = [];
        $like = '%'.$needle.'%';

        // Actions matched against the query.
        foreach (self::ACTIONS as $a) {
            $label = $isAr ? $a['label_ar'] : $a['label_en'];
            if (stripos($label, $needle) !== false || stripos($a['label_en'], $needle) !== false) {
                $out[] = [
                    'kind' => 'action',
                    'label' => $a['icon'].'  '.$label,
                    'sub' => null,
                    'url' => route($a['route']),
                ];
            }
        }

        // Contracts (own).
        $contracts = Contract::query()
            ->where('user_id', $userId)
            ->where('title', 'like', $like)
            ->latest('updated_at')
            ->limit(5)
            ->get(['id', 'title', 'status']);
        foreach ($contracts as $c) {
            $out[] = [
                'kind' => 'contract',
                'label' => '📄  '.$c->title,
                'sub' => $isAr ? 'عقد · '.$c->status : 'Contract · '.$c->status,
                'url' => route('lawyer.contracts').'?contract='.$c->id,
            ];
        }

        // Matters (own).
        $matters = Matter::query()
            ->where('user_id', $userId)
            ->where(function ($q) use ($like) {
                $q->where('client_name', 'like', $like)
                    ->orWhere('matter_name', 'like', $like)
                    ->orWhere('reference', 'like', $like);
            })
            ->latest('updated_at')
            ->limit(5)
            ->get(['id', 'client_name', 'matter_name']);
        foreach ($matters as $m) {
            $out[] = [
                'kind' => 'matter',
                'label' => '📁  '.$m->client_name.' — '.$m->matter_name,
                'sub' => $isAr ? 'موضوع' : 'Matter',
                'url' => route('lawyer.matters'),
            ];
        }

        // Templates (own + system).
        $templates = ContractTemplate::query()
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhere('is_system', true);
            })
            ->where('name', 'like', $like)
            ->limit(5)
            ->get(['id', 'name', 'category']);
        foreach ($templates as $t) {
            $out[] = [
                'kind' => 'template',
                'label' => '🗎  '.$t->name,
                'sub' => $isAr ? 'قالب' : 'Template'.($t->category ? ' · '.$t->category : ''),
                'url' => route('lawyer.templates'),
            ];
        }

        // Clippings (own).
        $clips = LegalClipping::query()
            ->with('document:id,title')
            ->where('user_id', $userId)
            ->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                    ->orWhere('note', 'like', $like)
                    ->orWhere('snippet', 'like', $like);
            })
            ->latest('id')
            ->limit(5)
            ->get(['id', 'title', 'legal_document_id']);
        foreach ($clips as $cl) {
            $label = $cl->title ?? $cl->document?->title ?? 'Clipping';
            $out[] = [
                'kind' => 'clipping',
                'label' => '🔖  '.$label,
                'sub' => $isAr ? 'مقتطف' : 'Clipping',
                'url' => route('lawyer.law-show', ['docId' => $cl->legal_document_id]),
            ];
        }

        // Legal documents (corpus, public — already user-scoped or system).
        $docs = LegalDocument::query()
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->where('title', 'like', $like)
            ->limit(5)
            ->get(['id', 'title', 'jurisdiction']);
        foreach ($docs as $d) {
            $out[] = [
                'kind' => 'law',
                'label' => '⚖  '.$d->title,
                'sub' => $isAr ? 'قانون' : 'Law'.($d->jurisdiction ? ' · '.$d->jurisdiction : ''),
                'url' => route('lawyer.law-show', ['docId' => $d->id]),
            ];
        }

        return $out;
    }

    /**
     * @return array<int, array{kind:string, label:string, sub:?string, url:string}>
     */
    private function actionResults(bool $isAr): array
    {
        $out = [];
        foreach (self::ACTIONS as $a) {
            $out[] = [
                'kind' => 'action',
                'label' => $a['icon'].'  '.($isAr ? $a['label_ar'] : $a['label_en']),
                'sub' => null,
                'url' => route($a['route']),
            ];
        }

        return $out;
    }

    public function render()
    {
        return view('livewire.command-palette');
    }
}
