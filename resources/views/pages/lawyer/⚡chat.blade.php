<?php

use App\Models\ChatSession;
use App\Models\ContractTemplate;
use App\Services\Contracts\ContractDraftingService;
use App\Services\Contracts\PlanLimitException;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Drafting workspace')] class extends Component
{
    #[Url(as: 'session')]
    public ?int $sessionId = null;

    public string $newIntent = '';

    public ?int $newTemplateId = null;

    public string $reply = '';

    public bool $busy = false;

    public string $streamingReply = '';

    #[Computed]
    public function sessions()
    {
        return ChatSession::query()
            ->where('user_id', Auth::id())
            ->latest('updated_at')
            ->limit(50)
            ->get();
    }

    #[Computed]
    public function templates()
    {
        return ContractTemplate::query()
            ->where(function ($q) {
                $q->where('user_id', Auth::id())->orWhere('is_system', true);
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function activeSession(): ?ChatSession
    {
        if (! $this->sessionId) {
            return null;
        }

        return ChatSession::query()
            ->where('id', $this->sessionId)
            ->where('user_id', Auth::id())
            ->with('messages')
            ->first();
    }

    public function startSession(ContractDraftingService $svc): void
    {
        $this->validate(['newIntent' => 'required|string|min:10']);
        $template = $this->newTemplateId
            ? ContractTemplate::query()
                ->where('id', $this->newTemplateId)
                ->where(function ($q) {
                    $q->where('user_id', Auth::id())->orWhere('is_system', true);
                })
                ->first()
            : null;

        $this->busy = true;
        $this->streamingReply = '';
        try {
            $session = $svc->startSession(Auth::user(), $template, $this->newIntent);
            $svc->continueSessionStreaming(
                session: $session,
                userMessage: '',
                onDelta: function (string $delta): void {
                    $this->stream(to: 'reply-stream', content: $delta, replace: false);
                },
            );
            $this->sessionId = $session->id;
            $this->newIntent = '';
            $this->newTemplateId = null;
            unset($this->sessions, $this->activeSession);
        } catch (PlanLimitException $e) {
            // Friendly upgrade prompt instead of generic error toast.
            Flux::toast(
                variant: 'warning',
                heading: __('Monthly draft limit reached'),
                text: __('You have used :used of :limit drafts on the :plan plan this month. Upgrade to keep drafting, or wait until the period resets.', [
                    'used' => $e->used,
                    'limit' => $e->limit,
                    'plan' => ucfirst($e->plan),
                ]),
                duration: 8000,
            );
        } catch (Throwable $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        } finally {
            $this->busy = false;
            $this->streamingReply = '';
        }
    }

    public function sendReply(ContractDraftingService $svc): void
    {
        $this->validate(['reply' => 'required|string|min:1']);
        $session = $this->activeSession;
        if (! $session) {
            return;
        }

        $userMessage = $this->reply;
        $this->reply = '';
        $this->busy = true;
        $this->streamingReply = '';

        try {
            $svc->continueSessionStreaming(
                session: $session,
                userMessage: $userMessage,
                onDelta: function (string $delta): void {
                    $this->streamingReply .= $delta;
                    $this->stream(to: 'reply-stream', content: $delta, replace: false);
                },
            );
            unset($this->activeSession);
        } catch (Throwable $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        } finally {
            $this->busy = false;
            $this->streamingReply = '';
        }
    }

    public function generateDraft(ContractDraftingService $svc): void
    {
        $session = $this->activeSession;
        if (! $session) {
            return;
        }
        $this->busy = true;
        try {
            $contract = $svc->finalize($session);
            unset($this->activeSession);

            // Don't leave the user stranded on the chat page — open the
            // contract they just drafted. The toast still fires through
            // the redirect so the success state is visible on arrival.
            Flux::toast(variant: 'success', text: __('Draft generated. Opening contract…'));
            $this->redirect(route('lawyer.contracts', ['contract' => $contract->id]), navigate: true);
        } catch (Throwable $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
            $this->busy = false;
        }
    }

    public function selectSession(int $id): void
    {
        $this->sessionId = $id;
        unset($this->activeSession);
    }

    public function newSession(): void
    {
        $this->sessionId = null;
        $this->newIntent = '';
        $this->newTemplateId = null;
    }
}; ?>

<div class="chat-shell" x-data="{ drawerOpen: false }" @keydown.escape.window="drawerOpen = false">

    {{-- Mobile topbar — visible only below lg.
         Surfaces session title + "sessions list" toggle + "+ new" action.
         Sits BELOW the Flux app header (which provides global sidebar
         toggle). The two are distinct: Flux's header opens the global
         app sidebar (Workspace / Contracts / etc.); our toggle opens
         the chat's per-session list. --}}
    <div class="chat-topbar">
        <button type="button" class="chat-iconbtn" @click="drawerOpen = !drawerOpen" aria-label="{{ __('Sessions') }}">
            <flux:icon.queue-list class="size-5" />
        </button>
        <h2 class="chat-topbar-title">
            {{ $this->activeSession?->title ?? __('New draft') }}
        </h2>
        <button type="button"
            wire:click="newSession"
            wire:loading.attr="disabled"
            wire:target="newSession"
            class="chat-iconbtn"
            aria-label="{{ __('New') }}">
            <flux:icon.plus class="size-5" wire:loading.remove wire:target="newSession" />
            <span wire:loading wire:target="newSession" class="lb-spinner" style="color: currentColor;"></span>
        </button>
    </div>

    {{-- Backdrop for mobile drawer --}}
    <div class="chat-sidebar-backdrop" :class="drawerOpen ? 'is-open' : ''" @click="drawerOpen = false" x-cloak></div>

    {{-- Sidebar — drawer on mobile, fixed column on lg+ --}}
    <aside class="chat-sidebar" :class="drawerOpen ? 'is-open' : ''">
        <div class="flex items-center justify-between border-b hairline px-4 py-3">
            <p class="font-mono text-[11px] uppercase tracking-[0.12em] text-zinc-500 font-semibold">{{ __('Sessions') }}</p>
            <x-loading-button
                size="xs"
                variant="ghost"
                wire-click="newSession"
                icon="plus"
                :busy-label="__('Creating…')"
            >
                {{ __('New') }}
            </x-loading-button>
        </div>
        <ul class="chat-session-list">
            @forelse ($this->sessions as $s)
                @php($isActive = $sessionId === $s->id)
                <li>
                    <button type="button"
                        wire:click="selectSession({{ $s->id }})"
                        wire:loading.attr="disabled"
                        wire:target="selectSession({{ $s->id }})"
                        @click="drawerOpen = false"
                        class="chat-session-item {{ $isActive ? 'is-active' : '' }}">
                        <span class="chat-session-title">{{ $s->title ?? __('Untitled session') }}</span>
                        <div class="chat-session-meta">
                            <span>{{ str_replace('_', ' ', $s->status) }}</span>
                            <span>{{ $s->updated_at?->diffForHumans() }}</span>
                        </div>
                    </button>
                </li>
            @empty
                <li class="p-6 text-center">
                    <p class="text-xs text-zinc-500">{{ __('No sessions yet.') }}</p>
                </li>
            @endforelse
        </ul>
    </aside>

    {{-- Main pane --}}
    <main class="chat-main">

        @if (! $this->activeSession)
            {{-- ───── Empty state — start a new draft ───── --}}
            <div class="chat-empty">
                <div class="chat-empty-card">
                    <span class="badge-tag">
                        <span class="pip"></span>{{ __('New draft') }}
                    </span>
                    <h1 class="display-serif mt-4 text-xl sm:text-2xl" style="line-height: 1.2;">
                        {{ __('Describe what you need to draft') }}
                    </h1>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        {{ __('We will ask you a few clarifying questions, ground the draft in the relevant law, and produce a citation-grounded contract.') }}
                    </p>

                    <form wire:submit="startSession" class="mt-5 space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Template (optional)') }}</flux:label>
                            <flux:select wire:model="newTemplateId">
                                <flux:select.option value="">{{ __('— None —') }}</flux:select.option>
                                @foreach ($this->templates as $t)
                                    <flux:select.option value="{{ $t->id }}">{{ $t->name }}@if ($t->category) — {{ $t->category }}@endif</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:description>{{ __('Templates speed up routine drafts. Skip for one-offs.') }}</flux:description>
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('What do you need to draft?') }}</flux:label>
                            <div class="speech-host" data-speech-host>
                                <flux:textarea
                                    wire:model="newIntent"
                                    placeholder="{{ __('e.g. An NDA between ACME Inc. and Globex Corp. for a 2-year mutual disclosure under Egyptian law.') }}"
                                    rows="5"
                                    required
                                    data-speech-target="new-intent"
                                    class="!font-sans !text-sm" />
                            </div>
                            <flux:description>{{ __('Include parties, jurisdiction, key terms. Arabic is fully supported. Tap the microphone to dictate.') }}</flux:description>
                        </flux:field>

                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <button type="button"
                                    class="speech-mic"
                                    data-speech-mic="new-intent"
                                    aria-label="{{ __('Dictate by voice') }}"
                                    hidden>
                                <svg class="speech-mic-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="9" y="2" width="6" height="12" rx="3"/>
                                    <path d="M5 11a7 7 0 0 0 14 0"/>
                                    <line x1="12" y1="18" x2="12" y2="22"/>
                                    <line x1="8" y1="22" x2="16" y2="22"/>
                                </svg>
                                <span class="speech-mic-label" data-label-idle>{{ __('Speak') }}</span>
                                <span class="speech-mic-label" data-label-active hidden>{{ __('Listening… tap to stop') }}</span>
                            </button>

                            <x-loading-button
                                type="submit"
                                variant="primary"
                                size="md"
                                wire-submit="startSession"
                                icon="arrow-right"
                                :busy-label="__('Starting…')"
                            >
                                {{ __('Begin') }}
                            </x-loading-button>
                        </div>
                        <p class="speech-status text-[11px] text-zinc-500" data-speech-status hidden></p>
                    </form>
                </div>
            </div>
        @else
            {{-- ───── Active session ───── --}}
            @php($session = $this->activeSession)
            @php($headerPill = [
                'drafted' => 'pill pill-success',
                'refused' => 'pill pill-warning',
                'drafting' => 'pill pill-info',
            ][$session->status] ?? 'pill pill-neutral')

            {{-- Desktop header (>= lg). Mobile uses chat-topbar above. --}}
            <header class="chat-header">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-[10px] uppercase tracking-[0.12em] text-zinc-500 font-semibold">{{ __('Drafting session') }}</span>
                        <span class="{{ $headerPill }}">{{ str_replace('_', ' ', $session->status) }}</span>
                    </div>
                    <h2 class="font-serif mt-1 text-base sm:text-lg leading-tight text-zinc-900 dark:text-zinc-50 truncate">{{ $session->title }}</h2>
                </div>
                <div class="flex items-center gap-2">
                    <x-loading-button
                        variant="ghost"
                        size="sm"
                        wire-click="newSession"
                        icon="plus"
                        :busy-label="__('Creating…')"
                    >
                        {{ __('New') }}
                    </x-loading-button>
                    <x-loading-button
                        variant="primary"
                        size="sm"
                        wire-click="generateDraft"
                        icon="sparkles"
                        :busy-label="__('Drafting…')"
                        :disabled="$busy"
                    >
                        {{ __('Generate draft') }}
                    </x-loading-button>
                </div>
            </header>

            {{-- Messages --}}
            <div class="chat-messages">
                @foreach ($session->messages as $m)
                    @php($isRefusal = ($m->metadata['kind'] ?? null) === 'refusal')
                    @php($isUser = $m->role === 'user')
                    <div class="chat-msg-row {{ $isUser ? 'is-user' : '' }} {{ $isRefusal ? 'is-refusal' : '' }}">
                        <div class="chat-msg-avatar">
                            @if ($isUser)
                                {{ str(auth()->user()->name)->substr(0,1)->upper() }}
                            @elseif ($isRefusal)
                                <flux:icon.exclamation-triangle class="size-4" />
                            @else
                                §
                            @endif
                        </div>
                        <div class="chat-msg-bubble"><pre>{{ $m->content }}</pre></div>
                    </div>
                @endforeach

                @if ($busy)
                    <div class="chat-msg-row">
                        <div class="chat-msg-avatar">§</div>
                        <div class="chat-msg-bubble">
                            <p class="chat-thinking">
                                <span class="chat-thinking-dot"></span>
                                {{ __('Thinking') }}
                            </p>
                            <pre wire:stream="reply-stream" style="margin:0"></pre>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Composer — WhatsApp/Telegram style.
                 Pill-shaped input on the start side with the mic inside.
                 Send button as a separate circle on the end side. Textarea
                 auto-grows from 1 line up to 6 lines, mirroring IM apps. --}}
            <form wire:submit="sendReply" class="chat-composer">
                <div class="chat-composer-row">
                    <label class="chat-composer-pill speech-host" data-speech-host>
                        <textarea
                            wire:model="reply"
                            rows="1"
                            placeholder="{{ __('Message') }}"
                            data-speech-target="reply"
                            x-data
                            x-init="
                                const fit = () => {
                                    $el.style.height = 'auto';
                                    $el.style.height = Math.min($el.scrollHeight, 144) + 'px';
                                };
                                fit();
                                $watch('$store.livewireDeltas', fit);
                            "
                            x-on:input="
                                $el.style.height = 'auto';
                                $el.style.height = Math.min($el.scrollHeight, 144) + 'px';
                            "
                            x-on:keydown.enter.prevent="
                                if (!$event.shiftKey) {
                                    $el.form?.requestSubmit();
                                }
                            "
                            x-on:keydown.enter.shift="
                                /* shift+enter inserts newline (default behavior) */
                            "
                            class="chat-composer-textarea"
                            aria-label="{{ __('Message') }}"
                        ></textarea>

                        <button type="button"
                                class="speech-mic speech-mic-compact chat-composer-mic"
                                data-speech-mic="reply"
                                aria-label="{{ __('Dictate by voice') }}"
                                hidden>
                            <svg class="speech-mic-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="9" y="2" width="6" height="12" rx="3"/>
                                <path d="M5 11a7 7 0 0 0 14 0"/>
                                <line x1="12" y1="18" x2="12" y2="22"/>
                                <line x1="8" y1="22" x2="16" y2="22"/>
                            </svg>
                        </button>
                    </label>

                    <button type="submit"
                            class="chat-composer-send"
                            wire:loading.attr="disabled"
                            wire:target="sendReply"
                            @disabled($busy)
                            aria-label="{{ __('Send') }}">
                        <svg wire:loading.remove wire:target="sendReply"
                             width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 2L11 13" />
                            <path d="M22 2L15 22L11 13L2 9L22 2Z" />
                        </svg>
                        <span wire:loading wire:target="sendReply" class="lb-spinner"></span>
                    </button>
                </div>
                <p class="speech-status mt-1.5 text-[11px] text-zinc-500" data-speech-status hidden></p>
            </form>
        @endif

    </main>

{{-- ─────────────────────────────────────────────────────────────────────
     Voice dictation — uses the browser-native Web Speech API. No server
     cost, no upstream provider, supports Arabic and English. Locale is
     auto-detected from <html dir>. The mic buttons reveal themselves only
     if the API is available; on unsupported browsers (Firefox without
     polyfill) the buttons stay hidden so the page degrades cleanly.
     CSS lives in resources/css/app.css (Vite-bundled, global).
     The JS is registered via @script so it survives Livewire DOM swaps.
     ────────────────────────────────────────────────────────────────── --}}

@script
<script>
(function () {
    // Chat-shell sizing — the chat is nested inside the app's outer
    // layout (sticky header on mobile + page padding). To make the
    // composer sit flush to the visible bottom of the viewport without
    // overflow, we measure the chat-shell's ACTUAL top offset on the
    // page and expose it as a CSS variable. The shell then uses:
    //     height: calc(100dvh - var(--app-chrome-offset))
    // We measure on first paint, after fonts load, on viewport resize,
    // and after Livewire DOM updates (composer/header swaps).
    function syncChromeOffset() {
        const shell = document.querySelector('.chat-shell');
        if (!shell) return;
        const desktop = window.matchMedia('(min-width: 1024px)').matches;
        // Force a layout if we don't have one yet
        const rect = shell.getBoundingClientRect();
        // Top distance from the viewport top to the shell's top = all the
        // chrome the chat doesn't own (app header + any page padding).
        // On desktop the shell starts at 0, so offset = 0.
        const offset = desktop ? 0 : Math.max(0, Math.round(rect.top));
        document.documentElement.style.setProperty('--app-chrome-offset', offset + 'px');
    }
    // Run twice: once immediately (before fonts) and once after fonts
    // load so we capture any header reflow caused by web-font swap.
    syncChromeOffset();
    requestAnimationFrame(syncChromeOffset);
    window.addEventListener('resize', syncChromeOffset);
    window.addEventListener('orientationchange', syncChromeOffset);
    if (document.fonts?.ready) {
        document.fonts.ready.then(syncChromeOffset);
    }
    document.addEventListener('livewire:navigated', syncChromeOffset);
    document.addEventListener('livewire:update', syncChromeOffset);

    // ── Browser feature detection ─────────────────────────────────────────
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    const isSecure = window.isSecureContext === true;
    const isAr = document.documentElement.dir === 'rtl';

    // i18n strings — defined early so the no-API / insecure-context paths
    // can use them too.
    const strings = isAr ? {
        permissionDenied: 'تم رفض إذن الميكروفون. فعّلوه في إعدادات المتصفح.',
        noSpeech: 'لم نسمع أي كلام — حاول مرة أخرى.',
        networkError: 'خطأ في الاتصال بخدمة التعرف على الصوت.',
        generic: 'تعذر تشغيل التعرف على الصوت.',
        listening: 'يستمع… تكلم الآن',
        ready: 'تم التعرف. اضغط إرسال.',
        unsupported: 'متصفحك لا يدعم التعرف على الصوت. جرب Chrome أو Edge أو Safari.',
        insecureContext: 'يحتاج الميكروفون إلى اتصال آمن (HTTPS). في التطوير المحلي، أضف http://my-lawyer.test إلى chrome://flags/#unsafely-treat-insecure-origin-as-secure ثم أعد تشغيل المتصفح.',
        insecureContextShort: 'الميكروفون يحتاج HTTPS — اضغط لتعليمات الإعداد.',
    } : {
        permissionDenied: 'Microphone permission denied. Enable it in your browser settings.',
        noSpeech: 'No speech detected — try again.',
        networkError: 'Speech-recognition service is unreachable.',
        generic: 'Could not start voice dictation.',
        listening: 'Listening… speak now',
        ready: 'Captured. Press send.',
        unsupported: 'Your browser does not support speech recognition. Try Chrome, Edge, or Safari.',
        insecureContext: 'Microphone access requires a secure connection (HTTPS). For local development, add http://my-lawyer.test to chrome://flags/#unsafely-treat-insecure-origin-as-secure and relaunch your browser.',
        insecureContextShort: 'Mic needs HTTPS — click for setup instructions.',
    };

    // ── Reveal a clear failure message when we can't proceed ─────────────
    // Two failure modes to communicate:
    //   1) No SpeechRecognition API at all (Firefox without polyfill)
    //   2) API present but blocked by Chrome because origin is not secure
    //
    // The mic stays VISIBLE in the UI in both cases — just visually marked
    // unavailable — so the user can see the feature exists and a click
    // reveals the reason. (Previously the compact reply-composer mic would
    // become disabled with no visual change, which looked exactly like a
    // missing-feature.)
    function showFatal(short, full) {
        document.querySelectorAll('[data-speech-mic]').forEach((btn) => {
            btn.hidden = false;
            btn.classList.add('speech-mic-disabled');
            btn.setAttribute('aria-disabled', 'true');
            btn.setAttribute('title', full);
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const node = document.querySelector('[data-speech-status]');
                if (node) {
                    node.hidden = false;
                    node.textContent = full;
                    node.setAttribute('data-state', 'error');
                } else {
                    alert(full);
                }
            });

            // Long-form label (empty-state composer): replace the visible text.
            const idle = btn.querySelector('[data-label-idle]');
            if (idle) idle.textContent = short;

            // Compact mic (reply composer): no visible label exists. Show
            // a tiny inline hint below the textarea so the user sees WHY
            // the mic looks different rather than guessing.
            if (btn.classList.contains('speech-mic-compact')) {
                const hint = document.querySelector('[data-speech-status]');
                if (hint && !hint.dataset.fatalShown) {
                    hint.dataset.fatalShown = '1';
                    hint.hidden = false;
                    hint.textContent = short;
                    hint.setAttribute('data-state', 'error');
                }
            }
        });
    }

    if (!SR) {
        showFatal(
            isAr ? 'غير متاح' : 'Unavailable',
            strings.unsupported,
        );
        return;
    }
    if (!isSecure) {
        // SpeechRecognition won't throw until .start() is called, but Chrome
        // silently rejects it on non-secure contexts. Surface the real
        // reason now so the user isn't left guessing.
        showFatal(strings.insecureContextShort, strings.insecureContext);
        return;
    }

    // ── Locale for recognition ────────────────────────────────────────────
    // Egypt-first: Arabic (Egypt) when the UI is in Arabic; American English
    // otherwise. The API silently falls back to a related locale if the
    // exact tag isn't installed on the device.
    const recognitionLocale = isAr ? 'ar-EG' : 'en-US';

    // ── State per button ──────────────────────────────────────────────────
    const sessions = new Map(); // target-id → { recognition, button, textarea, baseValue }

    function findTextareaForTarget(target) {
        // The textarea may be rendered by Flux inside a wrapper — find by
        // data-speech-target on the textarea itself, falling back to
        // descendants of any data-speech-host nearby.
        const direct = document.querySelector('textarea[data-speech-target="' + target + '"]');
        if (direct) return direct;
        const host = document.querySelector('[data-speech-host] textarea');
        return host || null;
    }

    function reveal(button) {
        button.hidden = false;
    }

    function setStatus(message, state) {
        const node = document.querySelector('[data-speech-status]');
        if (!node) return;
        if (!message) {
            node.hidden = true;
            node.textContent = '';
            node.removeAttribute('data-state');
            return;
        }
        node.hidden = false;
        node.textContent = message;
        if (state) node.setAttribute('data-state', state); else node.removeAttribute('data-state');
    }

    function setListening(button, on) {
        button.classList.toggle('is-listening', on);
        button.setAttribute('aria-pressed', on ? 'true' : 'false');
        const idle = button.querySelector('[data-label-idle]');
        const active = button.querySelector('[data-label-active]');
        if (idle && active) {
            idle.hidden = on;
            active.hidden = !on;
        }
    }

    // Commit a value to the Livewire-tracked textarea. Setting `.value`
    // alone won't sync; we dispatch an `input` event so wire:model picks
    // up the new value on the next sync cycle.
    function commitToTextarea(textarea, newValue) {
        textarea.value = newValue;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function buildSession(target, button) {
        const textarea = findTextareaForTarget(target);
        if (!textarea) return null;

        const recognition = new SR();
        recognition.lang = recognitionLocale;
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.maxAlternatives = 1;

        let baseValue = '';
        let interim = '';

        recognition.addEventListener('start', () => {
            baseValue = textarea.value || '';
            // Ensure trailing space so dictated text doesn't fuse with prior text.
            if (baseValue && !/\s$/.test(baseValue)) baseValue += ' ';
            setListening(button, true);
            setStatus(strings.listening, 'listening');
        });

        recognition.addEventListener('result', (event) => {
            let finalText = '';
            interim = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                const r = event.results[i];
                const t = r[0].transcript;
                if (r.isFinal) finalText += t;
                else interim += t;
            }
            if (finalText) {
                baseValue = (baseValue + finalText).replace(/\s+$/, '') + ' ';
            }
            commitToTextarea(textarea, baseValue + interim);
        });

        recognition.addEventListener('error', (event) => {
            const err = event.error || 'generic';
            const msg = err === 'not-allowed' || err === 'service-not-allowed'
                ? strings.permissionDenied
                : err === 'no-speech' ? strings.noSpeech
                : err === 'network'   ? strings.networkError
                :                       strings.generic;
            setStatus(msg, 'error');
            setListening(button, false);
        });

        recognition.addEventListener('end', () => {
            // If we have any final text captured, commit one last time
            // without the interim suffix and report ready.
            commitToTextarea(textarea, baseValue.replace(/\s+$/, ''));
            setListening(button, false);
            if (textarea.value.trim().length > 0) {
                setStatus(strings.ready, 'ready');
                // Auto-clear after a short interval so the status isn't sticky.
                setTimeout(() => setStatus('', null), 4000);
            } else {
                setStatus('', null);
            }
        });

        return { recognition, button, textarea };
    }

    function onMicClick(event) {
        const button = event.currentTarget;
        const target = button.getAttribute('data-speech-mic');
        if (!target) return;

        let session = sessions.get(target);
        if (!session) {
            session = buildSession(target, button);
            if (!session) return;
            sessions.set(target, session);
        }

        // Toggle: if already listening on this button, stop. Otherwise start
        // and stop any other active recognition first.
        if (button.classList.contains('is-listening')) {
            try { session.recognition.stop(); } catch (_) { /* noop */ }
            return;
        }

        sessions.forEach((s) => {
            if (s !== session && s.button.classList.contains('is-listening')) {
                try { s.recognition.stop(); } catch (_) { /* noop */ }
            }
        });

        try {
            session.recognition.start();
        } catch (_) {
            // Some browsers throw if start() is called too soon after stop().
            // Re-create the session and retry once.
            sessions.delete(target);
            const fresh = buildSession(target, button);
            if (!fresh) return;
            sessions.set(target, fresh);
            try { fresh.recognition.start(); } catch (_) { setStatus(strings.generic, 'error'); }
        }
    }

    // Wire-up runs on initial load AND on Livewire DOM updates (the chat
    // page swaps composer fragments as the session state changes).
    function wire() {
        document.querySelectorAll('[data-speech-mic]').forEach((btn) => {
            if (btn.dataset.speechBound === '1') return;
            btn.dataset.speechBound = '1';
            reveal(btn);
            btn.addEventListener('click', onMicClick);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', wire);
    } else {
        wire();
    }
    document.addEventListener('livewire:navigated', wire);
    document.addEventListener('livewire:update', wire);
})();
</script>
@endscript

</div>
