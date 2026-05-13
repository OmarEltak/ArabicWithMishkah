<?php

declare(strict_types=1);

use App\Models\AuditLog;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\User;
use App\Services\AI\CitationVerifier;
use App\Services\AI\EmbeddingService;
use App\Services\AI\LawLookupTool;
use App\Services\AI\LlmInterface;
use App\Services\AI\RagService;
use App\Services\Contracts\ContractDraftingService;
use App\Services\Ingestion\EastlawsIngestService;
use App\Services\Verification\CorpusCitationGate;

/**
 * Tests for the clarifying-vs-draft routing logic introduced when fixing
 * the "AI skips clarifying questions and dumps a draft" bug, and the
 * editDraft() path introduced when fixing the "AI regenerates instead of
 * editing on reply" bug.
 *
 * The LLM is stubbed via FakeLlm so tests run in milliseconds, deterministic
 * and free of upstream credits.
 */

/* ─────────────────────────────────────────────────────────────────────
   FakeLlm: scripted responses keyed by what the prompt looks like.
   ────────────────────────────────────────────────────────────────── */

class FakeLlm implements LlmInterface
{
    /** @var array<int, array{role:string,content:string}> */
    public array $lastMessages = [];
    /** @var array<string,mixed> */
    public array $lastOptions = [];

    public function __construct(private readonly array $scripted = []) {}

    public function chat(array $messages, array $options = []): array
    {
        $this->lastMessages = $messages;
        $this->lastOptions = $options;

        // Decide reply by detecting tag markers we inject in the prompt:
        //   - clarifying call has system prompt mentioning "ASK FOCUSED CLARIFYING"
        //   - draft call has system prompt with "lookup_law"
        //   - edit call has user message wrapping "<CONTRACT>"
        $systemText = (string) ($options['system'] ?? '');
        $userText = '';
        foreach ($messages as $m) {
            if (is_string($m['content'] ?? null)) {
                $userText .= "\n".$m['content'];
            }
        }

        if (str_contains($userText, '<CONTRACT>')) {
            return ['content' => $this->scripted['edit'] ?? 'EDITED-CONTRACT-BODY'];
        }
        if (str_contains($systemText, 'ASK FOCUSED CLARIFYING') || str_contains($systemText, 'gather just enough')) {
            return ['content' => $this->scripted['clarifying'] ?? '{"questions":["Who are the parties?","What is the governing law?"],"ready_to_draft":false,"collected_facts":{}}'];
        }
        // Draft path
        return ['content' => $this->scripted['draft'] ?? 'DRAFTED-CONTRACT-BODY'];
    }

    public function chatStream(array $messages, array $options, callable $onDelta): array
    {
        $response = $this->chat($messages, $options);
        $onDelta($response['content']);
        return $response;
    }

    public function isConfigured(): bool
    {
        return true;
    }
}

/* ─────────────────────────────────────────────────────────────────────
   Service-builder helper: build a ContractDraftingService with the fake
   LLM injected at every position that takes one.
   ────────────────────────────────────────────────────────────────── */

function makeServiceWithFakeLlm(FakeLlm $llm): ContractDraftingService
{
    $rag = RagService::fromConfig();
    $lookup = new LawLookupTool($rag, EastlawsIngestService::fromConfig());
    $verifier = new CitationVerifier(EmbeddingService::fromConfig());
    $gate = new CorpusCitationGate();

    return new ContractDraftingService(
        llm: $llm,
        rag: $rag,
        lookup: $lookup,
        verifier: $verifier,
        corpusGate: $gate,
        maxClarifyingRounds: (int) config('lawyer.max_clarifying_questions', 3),
        maxToolRounds: 1,
    );
}

function makeSessionWithDraft(User $user, ?ContractTemplate $template, string $body = "Article 1 — Parties\n\nArticle 2 — Term\n\nArticle 3 — Governing Law"): array
{
    $session = ChatSession::create([
        'user_id' => $user->id,
        'contract_template_id' => $template?->id,
        'title' => 'Test session',
        'status' => 'drafted',
        'collected_facts' => [],
        'open_questions' => [],
    ]);
    ChatMessage::create([
        'chat_session_id' => $session->id,
        'role' => 'user',
        'content' => 'Initial intent text',
    ]);
    ChatMessage::create([
        'chat_session_id' => $session->id,
        'role' => 'assistant',
        'content' => $body,
        'metadata' => ['kind' => 'draft'],
    ]);
    $contract = Contract::create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'contract_template_id' => $template?->id,
        'title' => $session->title,
        'body' => $body,
        'status' => 'draft',
        'version' => 1,
    ]);

    return [$session, $contract];
}

/* ─────────────────────────────────────────────────────────────────────
   Clarifying-routing tests
   ────────────────────────────────────────────────────────────────── */

it('asks clarifying questions when max_clarifying_questions > 0 on turn 1', function () {
    config()->set('lawyer.max_clarifying_questions', 3);

    $llm = new FakeLlm([
        'clarifying' => '{"questions":["Who are the parties?","Governing law?","Term?"],"ready_to_draft":false,"collected_facts":{}}',
    ]);
    $svc = makeServiceWithFakeLlm($llm);
    $user = User::factory()->create();

    $session = $svc->startSession($user, null, 'Short intent — needs more info');
    $msg = $svc->continueSession($session);

    expect($msg->metadata['kind'] ?? null)->toBe('clarifying');
    expect($session->fresh()->status)->toBe('clarifying');
    expect($msg->content)->toContain('Who are the parties?');
});

it('routes to produceDraft (not clarifying) when max_clarifying_questions = 0', function () {
    config()->set('lawyer.max_clarifying_questions', 0);

    $llm = new FakeLlm(['draft' => 'STRAIGHT-TO-DRAFT-BODY']);
    $svc = makeServiceWithFakeLlm($llm);
    $user = User::factory()->create();

    $session = $svc->startSession($user, null, 'Detailed intent with everything');
    $msg = $svc->continueSession($session);

    // The legacy behavior: routing SKIPS the clarifying path and lands on
    // produceDraft. produceDraft's own tool-use semantics are exercised
    // elsewhere (DraftingFlowTest); here we only verify the router did not
    // pick the clarifying branch.
    expect($msg->metadata['kind'] ?? null)->not->toBe('clarifying');
    expect($session->fresh()->status)->not->toBe('clarifying');
});

it('routes the second reply to editDraft when a draft already exists', function () {
    config()->set('lawyer.max_clarifying_questions', 3);

    $llm = new FakeLlm([
        'edit' => "Article 1 — Parties\n\nArticle 2 — Term\n\nArticle 3 — Confidentiality\n\nArticle 4 — Governing Law",
    ]);
    $svc = makeServiceWithFakeLlm($llm);
    $user = User::factory()->create();
    [$session, $contract] = makeSessionWithDraft($user, null);

    $msg = $svc->continueSession($session, 'Add a confidentiality clause as Article 3 and renumber.');

    expect($msg->metadata['kind'] ?? null)->toBe('edit');
    expect($msg->content)->toContain('Confidentiality');
    expect($contract->fresh()->body)->toContain('Confidentiality');
    expect($contract->fresh()->version)->toBe(2);
});

/* ─────────────────────────────────────────────────────────────────────
   editDraft behavior tests
   ────────────────────────────────────────────────────────────────── */

it('snapshots the prior body into body_history on edit', function () {
    config()->set('lawyer.max_clarifying_questions', 3);
    $llm = new FakeLlm(['edit' => 'NEW BODY AFTER EDIT']);
    $svc = makeServiceWithFakeLlm($llm);
    $user = User::factory()->create();
    [$session, $contract] = makeSessionWithDraft($user, null, 'OLD BODY BEFORE EDIT');

    $svc->continueSession($session, 'change the wording');

    $fresh = $contract->fresh();
    expect($fresh->body_history)->toBeArray()->toHaveCount(1);
    expect($fresh->body_history[0]['body'])->toBe('OLD BODY BEFORE EDIT');
    expect($fresh->body_history[0]['version'])->toBe(1);
    expect($fresh->body_history[0]['edit_request'])->toBe('change the wording');
    expect($fresh->body)->toBe('NEW BODY AFTER EDIT');
});

it('bumps version on each edit', function () {
    config()->set('lawyer.max_clarifying_questions', 3);
    $llm = new FakeLlm(['edit' => 'V2 BODY']);
    $svc = makeServiceWithFakeLlm($llm);
    $user = User::factory()->create();
    [$session, $contract] = makeSessionWithDraft($user, null);

    $svc->continueSession($session, 'edit 1');
    expect($contract->fresh()->version)->toBe(2);

    $llm2 = new FakeLlm(['edit' => 'V3 BODY']);
    $svc2 = makeServiceWithFakeLlm($llm2);
    $svc2->continueSession($session->fresh(), 'edit 2');
    expect($contract->fresh()->version)->toBe(3);
});

it('returns edit_noop without LLM call when edit instruction is empty', function () {
    config()->set('lawyer.max_clarifying_questions', 3);
    $llm = new FakeLlm(['edit' => 'SHOULD-NOT-BE-CALLED']);
    $svc = makeServiceWithFakeLlm($llm);
    $user = User::factory()->create();
    [$session, $contract] = makeSessionWithDraft($user, null);

    $msg = $svc->continueSession($session, '');

    expect($msg->metadata['kind'] ?? null)->toBe('edit_noop');
    expect($contract->fresh()->version)->toBe(1); // unchanged
});

it('captures SORRY: refusal as edit_refusal kind without modifying contract', function () {
    config()->set('lawyer.max_clarifying_questions', 3);
    $llm = new FakeLlm(['edit' => 'SORRY: the edit request is too ambiguous.']);
    $svc = makeServiceWithFakeLlm($llm);
    $user = User::factory()->create();
    [$session, $contract] = makeSessionWithDraft($user, null, 'ORIGINAL BODY');

    $msg = $svc->continueSession($session, 'make it better somehow');

    expect($msg->metadata['kind'] ?? null)->toBe('edit_refusal');
    expect($msg->content)->toStartWith('SORRY:');
    expect($contract->fresh()->body)->toBe('ORIGINAL BODY');
    expect($contract->fresh()->version)->toBe(1);
});

it('logs contract.edited to the audit chain on successful edit', function () {
    config()->set('lawyer.max_clarifying_questions', 3);
    $llm = new FakeLlm(['edit' => 'EDITED BODY']);
    $svc = makeServiceWithFakeLlm($llm);
    $user = User::factory()->create();
    [$session, $contract] = makeSessionWithDraft($user, null);

    $svc->continueSession($session, 'add a clause');

    $audit = AuditLog::where('action', 'contract.edited')->latest('id')->first();
    expect($audit)->not->toBeNull();
    expect($audit->subject_id)->toBe($contract->id);
    expect($audit->metadata['edit_request'] ?? null)->toBe('add a clause');
    // HMAC chain fields populated for tamper-evidence
    expect($audit->content_hmac)->toBeString()->toHaveLength(64);
});
