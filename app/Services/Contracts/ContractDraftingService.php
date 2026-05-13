<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\User;
use App\Services\AI\CitationVerifier;
use App\Services\AI\FactExtractor;
use App\Services\AI\LawLookupTool;
use App\Services\AI\LlmFactory;
use App\Services\AI\LlmInterface;
use App\Services\AI\RagService;
use App\Services\Audit\AuditLogger;
use App\Services\Ingestion\EastlawsIngestService;
use App\Services\Verification\CorpusCitationGate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContractDraftingService
{
    public function __construct(
        private readonly LlmInterface $llm,
        private readonly RagService $rag,
        private readonly LawLookupTool $lookup,
        private readonly CitationVerifier $verifier,
        private readonly CorpusCitationGate $corpusGate,
        private readonly int $maxClarifyingRounds = 5,
        private readonly int $maxToolRounds = 4,
    ) {}

    public static function fromConfig(): self
    {
        $rag = RagService::fromConfig();

        return new self(
            llm: LlmFactory::default(),
            rag: $rag,
            lookup: new LawLookupTool($rag, EastlawsIngestService::fromConfig()),
            verifier: CitationVerifier::fromConfig(),
            corpusGate: new CorpusCitationGate,
            maxClarifyingRounds: (int) config('lawyer.max_clarifying_questions', 5),
            maxToolRounds: (int) config('lawyer.max_tool_rounds', 4),
        );
    }

    /**
     * Start a new drafting session.
     *
     * Gates on the user's plan cap BEFORE creating any records: if the cap
     * is hit, PlanLimitException bubbles up so the UI can show an upgrade
     * prompt instead of an empty session. Edits to existing drafts skip
     * this gate (they don't consume new-draft quota).
     */
    public function startSession(User $user, ?ContractTemplate $template, string $intent): ChatSession
    {
        app(PlanUsageGate::class)->recordDraftAttempt($user);

        $session = ChatSession::create([
            'user_id' => $user->id,
            'contract_template_id' => $template?->id,
            'title' => Str::limit($intent, 80),
            'status' => 'clarifying',
            'collected_facts' => [],
            'open_questions' => [],
        ]);

        ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'user',
            'content' => $intent,
        ]);

        return $session;
    }

    /**
     * Append a user message and run one assistant turn.
     *
     * Routing (in priority order):
     *   1. If the session already has a draft (status == 'drafted' and a
     *      Contract row exists) → route to editDraft() to APPLY the user's
     *      changes to the existing contract rather than regenerating.
     *   2. If shouldDraft (max rounds reached or facts complete) →
     *      produceDraft() generates a fresh contract from the conversation.
     *   3. Otherwise → askClarifyingQuestions() collects missing info.
     */
    public function continueSession(ChatSession $session, string $userMessage = ''): ChatMessage
    {
        if ($userMessage !== '') {
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'role' => 'user',
                'content' => $userMessage,
            ]);
        }

        if ($this->sessionHasDraft($session)) {
            return $this->editDraft($session, $userMessage);
        }

        $rounds = $session->messages()->where('role', 'assistant')->count();
        $shouldDraft = $rounds >= $this->maxClarifyingRounds || $this->factsAppearComplete($session);

        return $shouldDraft
            ? $this->produceDraft($session)
            : $this->askClarifyingQuestions($session);
    }

    /**
     * Streaming variant. Same routing as continueSession() — edit path goes
     * to editDraft (also non-streamed for now since it needs the existing
     * body in context), clarifying streams token-by-token, drafting batches.
     */
    public function continueSessionStreaming(ChatSession $session, string $userMessage, callable $onDelta): ChatMessage
    {
        if ($userMessage !== '') {
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'role' => 'user',
                'content' => $userMessage,
            ]);
        }

        if ($this->sessionHasDraft($session)) {
            $msg = $this->editDraft($session, $userMessage);
            $onDelta($msg->content);

            return $msg;
        }

        $rounds = $session->messages()->where('role', 'assistant')->count();
        $shouldDraft = $rounds >= $this->maxClarifyingRounds || $this->factsAppearComplete($session);

        if ($shouldDraft) {
            // Drafting requires the tool-use loop — no incremental streaming.
            // Emit the final text as a single delta when produceDraft returns
            // so the UI's stream callback still receives the content.
            $msg = $this->produceDraft($session);
            $onDelta($msg->content);

            return $msg;
        }

        return $this->askClarifyingQuestionsStreaming($session, $onDelta);
    }

    /**
     * True when the session already has a generated contract draft and the
     * next user reply should be treated as an edit instruction rather than
     * a fresh-draft request.
     */
    private function sessionHasDraft(ChatSession $session): bool
    {
        if ($session->status !== 'drafted') {
            return false;
        }

        return Contract::where('chat_session_id', $session->id)->exists();
    }

    /**
     * Apply the user's natural-language edit request to the most recent
     * draft of this session. Examples of edit requests:
     *
     *   - "أريد إضافة ثلاثة بنود عن الإنهاء المبكر، التعويض، وسرية البيانات"
     *   - "Increase the liability cap to 24 months of fees"
     *   - "Remove clause 5 entirely"
     *
     * Strategy:
     *   1. Load the latest Contract.body for this session
     *   2. Snapshot the prior body into Contract.body_history (versioning)
     *   3. Send the LLM: existing body + user's edit instruction
     *   4. Get the full updated body back, persist as Contract.body, bump version
     *   5. Re-run CorpusCitationGate against the new body
     *   6. Create an assistant ChatMessage with the new body so the chat thread
     *      shows the updated draft inline
     *
     * We deliberately ask the LLM for the FULL updated contract rather than a
     * diff, because most users want to see the resulting document, and the
     * model has been shown to produce more coherent legal text in full-doc
     * mode than in patch/diff mode.
     */
    private function editDraft(ChatSession $session, string $editInstruction): ChatMessage
    {
        // Defence in depth: the user-typed edit instruction is interpolated
        // into the <EDIT_REQUEST> block below. Sanitise it so a hostile
        // user can't insert closing tags and start a new <SYSTEM> block
        // (prompt injection). The model is already trained to treat the
        // user turn as data, but stripping breaker tokens removes the
        // most-common injection vectors before the LLM ever sees them.
        $editInstruction = self::sanitizeUserInstruction($editInstruction);

        $contract = Contract::where('chat_session_id', $session->id)
            ->latest('id')
            ->first();

        if (! $contract) {
            // Defensive: sessionHasDraft said yes but no Contract row found.
            // Fall back to a fresh draft so the user isn't stranded.
            return $this->produceDraft($session);
        }

        $existingBody = (string) $contract->body;
        $locale = app()->getLocale();
        $isAr = $locale === 'ar';

        $system = <<<'SYS'
You are a senior legal drafting assistant editing an EXISTING contract.

INPUT: you receive the full current contract body in a <CONTRACT> block, then
the user's edit instruction in an <EDIT_REQUEST> block.

YOUR JOB:
1. Apply ONLY the changes the user requested. Do not rewrite untouched clauses.
2. If the user asks to ADD clauses, integrate them in the correct numerical order
   and re-number subsequent clauses to keep the sequence continuous.
3. If the user asks to MODIFY a clause, keep its position and number but update
   the wording. Preserve every citation that still applies; remove citations the
   change invalidates.
4. If the user asks to REMOVE a clause, delete it and re-number subsequent ones.
5. Preserve the document's language (Arabic in / Arabic out, English in / English
   out). Preserve formatting style (plain text, "البند" numbering, etc.).
6. Output the FULL updated contract text — every clause, every signature line.
   Do NOT output a diff, a patch, or commentary. Just the new contract body.
7. If the user's edit instruction is ambiguous or impossible to apply safely
   (e.g. "make it shorter" with no specifics), output a refusal that starts with
   the exact token: SORRY:  followed by what additional info you need.

NEVER:
- Invent citations that weren't in the original draft.
- Change the parties, dates, or governing law unless the user explicitly asked.
- Add a "DRAFT NOTES" or "Changes:" footer — the application surfaces the diff
  separately.
SYS;

        $userMessage = "<CONTRACT>\n{$existingBody}\n</CONTRACT>\n\n<EDIT_REQUEST>\n{$editInstruction}\n</EDIT_REQUEST>";
        if ($editInstruction === '') {
            // No fresh instruction — the user clicked send with an empty
            // textarea. Just re-affirm the existing draft.
            return ChatMessage::create([
                'chat_session_id' => $session->id,
                'role' => 'assistant',
                'content' => $existingBody,
                'metadata' => [
                    'kind' => 'edit_noop',
                    'contract_id' => $contract->id,
                ],
            ]);
        }

        $response = $this->llm->chat(
            [['role' => 'user', 'content' => $userMessage]],
            [
                'system' => $system,
                'max_tokens' => (int) config('lawyer.draft_max_tokens', 8000),
                'temperature' => 0.2,
            ],
        );

        $newBody = trim((string) ($response['content'] ?? ''));

        if ($newBody === '') {
            return ChatMessage::create([
                'chat_session_id' => $session->id,
                'role' => 'assistant',
                'content' => $isAr
                    ? 'تعذر تطبيق التعديل — لم يُرجِع النموذج محتوى. جرب صياغة الطلب بشكل أوضح.'
                    : 'Could not apply the edit — the model returned no content. Try rephrasing the request.',
                'metadata' => ['kind' => 'edit_error', 'contract_id' => $contract->id],
            ]);
        }

        // Refusal path — preserve the original body, just surface the model's reason.
        if (str_starts_with($newBody, 'SORRY:')) {
            return ChatMessage::create([
                'chat_session_id' => $session->id,
                'role' => 'assistant',
                'content' => $newBody,
                'metadata' => ['kind' => 'edit_refusal', 'contract_id' => $contract->id],
            ]);
        }

        // Append the prior body to the version history so the lawyer can see
        // the diff and roll back from the UI. Schema matches the manual-edit
        // path (saved_at/saved_by) so the existing diff UI Just Works.
        // edit_request is a tiny extra field that lets the UI show "why" the
        // version was bumped, alongside who+when.
        $history = is_array($contract->body_history) ? $contract->body_history : [];
        array_unshift($history, [
            'version' => (int) ($contract->version ?? 1),
            'body' => $existingBody,
            'saved_at' => now()->toIso8601String(),
            'saved_by' => 'AI edit',
            'edit_request' => $editInstruction,
        ]);
        $history = array_slice($history, 0, 10);

        // Re-run the corpus gate on the new body. If citations broke, mark
        // status needs_review so the lawyer sees the flag.
        $jurisdiction = $this->resolveJurisdiction(
            $session,
            $newBody, // scan the body itself for Arabic country names
            is_array($session->collected_facts) ? $session->collected_facts : [],
        );
        $report = $this->corpusGate->verify($newBody, $jurisdiction);

        $contract->body = $newBody;
        $contract->body_history = $history;
        $contract->version = (int) ($contract->version ?? 1) + 1;
        $contract->status = $report->passes() ? $contract->status : 'needs_review';
        $contract->corpus_gate = [
            'passes' => $report->passes(),
            'confidence_pct' => $report->confidencePercent(),
            'verified' => $report->verifiedCount(),
            'uncertain' => $report->uncertainCount(),
            'unverified' => $report->unverifiedCount(),
            'unverified_citations' => $report->unverifiedCitations(),
            'uncertain_citations' => $report->uncertainCitations(),
        ];
        $contract->save();

        app(AuditLogger::class)->log(
            action: 'contract.edited',
            subject: $contract,
            summary: 'Draft edited · '.$report->summary(),
            metadata: [
                'session_id' => $session->id,
                'edit_request' => $editInstruction,
                'corpus_gate_passes' => $report->passes(),
                'corpus_gate_confidence' => $report->confidencePercent(),
                'new_version' => $contract->version,
                'body_length' => mb_strlen($newBody),
            ],
            userId: $session->user_id,
        );

        return ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'assistant',
            'content' => $newBody,
            'metadata' => [
                'kind' => 'edit',
                'contract_id' => $contract->id,
                'version' => $contract->version,
                'citation_summary' => [
                    'verified' => $report->verifiedCount(),
                    'uncertain' => $report->uncertainCount(),
                    'unverified' => $report->unverifiedCount(),
                    'total' => $report->totalCitations(),
                ],
            ],
        ]);
    }

    /**
     * Force the assistant to produce a final draft now, regardless of clarifying state.
     */
    public function finalize(ChatSession $session): Contract
    {
        $msg = $this->produceDraft($session);
        $contract = Contract::where('chat_session_id', $session->id)->latest('id')->first();
        if (! $contract) {
            // produceDraft always creates a contract; fall back to manual creation.
            $contract = Contract::create([
                'user_id' => $session->user_id,
                'chat_session_id' => $session->id,
                'contract_template_id' => $session->contract_template_id,
                'title' => $session->title ?? 'Untitled contract',
                'body' => $msg->content,
            ]);
        }

        return $contract;
    }

    /**
     * Defang user-typed text that gets interpolated into a structured
     * prompt (e.g. inside <EDIT_REQUEST>...</EDIT_REQUEST>). Strips the
     * common prompt-injection breaker tokens and caps length so a hostile
     * input can't end the surrounding block and start a fresh <SYSTEM>
     * block, nor exhaust the context window.
     *
     * Intentionally NOT a generic XSS / HTML sanitiser — this is solely
     * about prompt structural tokens. Legitimate angle-brackets in user
     * input (e.g. "duration < 2 years") survive untouched.
     */
    public static function sanitizeUserInstruction(string $input): string
    {
        // Normalise: strip BOM, strip C0 control chars except tab/newline.
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $input) ?? '';

        // Token blacklist — case-insensitive. Covers our own tags plus the
        // chat-template / instruct-template tokens used by major model
        // families (ChatML, Llama-2 instruct, Anthropic XML tags).
        $blacklist = [
            '<CONTRACT>', '</CONTRACT>',
            '<EDIT_REQUEST>', '</EDIT_REQUEST>',
            '<SYSTEM>', '</SYSTEM>',
            '<USER>', '</USER>',
            '<ASSISTANT>', '</ASSISTANT>',
            '<INSTRUCTIONS>', '</INSTRUCTIONS>',
            '<TOOL>', '</TOOL>',
            '[INST]', '[/INST]',
            '<<SYS>>', '<</SYS>>',
            '<|im_start|>', '<|im_end|>',
            '<|system|>', '<|user|>', '<|assistant|>',
            '<|endoftext|>',
        ];
        foreach ($blacklist as $token) {
            $clean = str_ireplace($token, '', $clean);
        }

        // Strip lines that are obvious role-impersonation attempts at the
        // very start of a line — "SYSTEM:", "USER:", "ASSISTANT:" with
        // optional whitespace.
        $clean = preg_replace('/^\s*(SYSTEM|USER|ASSISTANT|INSTRUCTIONS?)\s*[:>]/im', '', $clean) ?? $clean;

        // Hard cap to keep the prompt within budget and to limit any
        // exotic injection payload size. 8k chars ≈ 2k tokens.
        $maxChars = 8000;
        if (mb_strlen($clean) > $maxChars) {
            $clean = mb_substr($clean, 0, $maxChars).' […truncated]';
        }

        return trim($clean);
    }

    /**
     * @return array<int, array{role:string, content:string}>
     */
    private function buildHistory(ChatSession $session): array
    {
        $msgs = $session->messages()->get(['role', 'content']);
        $history = [];
        foreach ($msgs as $m) {
            // Anthropic only accepts user/assistant in messages array; system goes separately.
            if (in_array($m->role, ['user', 'assistant'], true)) {
                $history[] = ['role' => $m->role, 'content' => $m->content];
            }
        }

        return $history;
    }

    /**
     * Build the LEGAL_CONTEXT block from RAG.
     *
     * Scoped by jurisdiction whenever one is known — otherwise top-K can
     * surface a high-cosine match from a different jurisdiction (e.g. an
     * Omani Royal Decree when the user is drafting an Egyptian contract)
     * which the LLM then mentions in clarifying questions and confuses
     * the user.
     *
     * Jurisdiction is resolved in priority order:
     *   1. session->template->jurisdiction (most specific)
     *   2. collected_facts['jurisdiction'] / collected_facts['governing_law']
     *   3. heuristic: scan the intent text for an ISO-2 country code
     *      or an Arabic country name like "مصر" / "السعودية"
     *
     * When no jurisdiction can be resolved, we return EMPTY context rather
     * than top-K-across-everything — better to ask the user than to leak
     * irrelevant law into the prompt.
     */
    private function ragContext(ChatSession $session): string
    {
        $intent = $session->messages()->where('role', 'user')->orderBy('id')->first()?->content ?? '';
        $facts = is_array($session->collected_facts) ? $session->collected_facts : [];
        $factSummary = '';
        foreach ($facts as $k => $v) {
            $factSummary .= '- '.$k.': '.(is_scalar($v) ? (string) $v : json_encode($v))."\n";
        }
        $query = trim($intent."\n".$factSummary);

        $jurisdiction = $this->resolveJurisdiction($session, $intent, $facts);

        // No-jurisdiction queries return empty context (we deliberately
        // refuse to leak cross-jurisdiction matches into the prompt).
        if ($jurisdiction === null) {
            return '';
        }

        $results = $this->rag->search($query, filters: ['jurisdiction' => $jurisdiction]);

        return $this->rag->formatContext($results);
    }

    /**
     * @param  array<string,mixed>  $facts
     */
    private function resolveJurisdiction(ChatSession $session, string $intent, array $facts): ?string
    {
        if ($iso = $session->template?->jurisdiction) {
            return strtoupper((string) $iso);
        }
        foreach (['jurisdiction', 'governing_law', 'country', 'iso_country'] as $key) {
            if (! empty($facts[$key]) && is_string($facts[$key])) {
                $candidate = strtoupper(substr(trim($facts[$key]), 0, 2));
                if (preg_match('/^[A-Z]{2}$/', $candidate)) {
                    return $candidate;
                }
            }
        }

        // Heuristic: Arabic country names → ISO-2.
        $map = [
            'مصر' => 'EG', 'مصري' => 'EG', 'القاهرة' => 'EG',
            'السعودية' => 'SA', 'السعودي' => 'SA', 'الرياض' => 'SA',
            'الإمارات' => 'AE', 'الامارات' => 'AE', 'دبي' => 'AE', 'أبوظبي' => 'AE',
            'الكويت' => 'KW', 'الكويتي' => 'KW',
            'قطر' => 'QA', 'قطري' => 'QA',
            'البحرين' => 'BH', 'البحريني' => 'BH',
            'عمان' => 'OM', 'عُمان' => 'OM', 'العماني' => 'OM',
            'الأردن' => 'JO', 'الاردن' => 'JO',
            'لبنان' => 'LB', 'لبناني' => 'LB',
            'تونس' => 'TN', 'تونسي' => 'TN',
            'ليبيا' => 'LY', 'ليبي' => 'LY',
        ];
        foreach ($map as $needle => $iso) {
            if (mb_stripos($intent, $needle) !== false) {
                return $iso;
            }
        }

        return null; // unknown — caller will return empty context
    }

    private function askClarifyingQuestions(ChatSession $session): ChatMessage
    {
        $template = $session->template;
        $required = is_array($template?->required_fields) ? $template->required_fields : [];
        $context = $this->ragContext($session);

        $system = <<<'SYS'
You are a senior legal drafting assistant working with a licensed attorney.

Goal: gather just enough facts to draft an accurate contract. ASK FOCUSED CLARIFYING
QUESTIONS when you do not have enough information. Do NOT draft the contract yet.

Rules:
- Output ONLY valid JSON. No prose outside JSON.
- Use the schema:
  {
    "questions": [string, ...],   // 1-5 questions, prioritized, plain language
    "ready_to_draft": boolean,    // true ONLY when you have all required facts
    "collected_facts": object     // key/value facts you can already extract from the conversation
  }
- The user can ONLY answer questions in chat. They CANNOT upload documents,
  attach files, or share screens. Never ask them to "upload", "attach", or
  "share" anything — only to type or state a fact.
- If you need information that's outside the scope of what the user can
  reasonably provide in chat (e.g. a specific statute text), DO NOT ask
  for it. Instead, set sensible defaults from the user's stated
  jurisdiction and proceed.
- Do NOT invent facts the lawyer has not stated.
- If LEGAL_CONTEXT is present, use it to inform your questions and the
  eventual draft. Do not refer to LEGAL_CONTEXT explicitly to the user
  (they did not provide it — it is retrieved internally).
SYS;

        $promptParts = [];
        if ($template) {
            $promptParts[] = 'TEMPLATE: '.$template->name.($template->category ? ' ('.$template->category.')' : '');
            if ($template->description) {
                $promptParts[] = 'DESCRIPTION: '.$template->description;
            }
            if (! empty($required)) {
                $promptParts[] = 'REQUIRED FIELDS: '.implode(', ', array_keys($required));
            }
        }
        if ($context !== '') {
            $promptParts[] = "LEGAL_CONTEXT (top-K retrieved):\n".$context;
        }
        $promptParts[] = 'Now decide whether to ask questions or signal ready_to_draft. Output JSON only.';

        $messages = $this->buildHistory($session);
        $messages[] = ['role' => 'user', 'content' => implode("\n\n", $promptParts)];

        $response = $this->llm->chat($messages, [
            'system' => $system,
            'max_tokens' => 1024,
            'temperature' => 0.2,
        ]);

        return $this->finalizeClarifyingResponse($session, $response['content']);
    }

    /**
     * Streaming counterpart. Emits raw JSON deltas to $onDelta as they arrive
     * (the chat UI hides the JSON envelope and only renders the final
     * humanised question list once the stream completes), then finalises the
     * same way askClarifyingQuestions() does.
     */
    private function askClarifyingQuestionsStreaming(ChatSession $session, callable $onDelta): ChatMessage
    {
        $template = $session->template;
        $required = is_array($template?->required_fields) ? $template->required_fields : [];
        $context = $this->ragContext($session);

        $system = <<<'SYS'
You are a senior legal drafting assistant working with a licensed attorney.

Goal: gather just enough facts to draft an accurate contract. ASK FOCUSED CLARIFYING
QUESTIONS when you do not have enough information. Do NOT draft the contract yet.

Rules:
- Output ONLY valid JSON. No prose outside JSON.
- Use the schema:
  {
    "questions": [string, ...],   // 1-5 questions, prioritized, plain language
    "ready_to_draft": boolean,    // true ONLY when you have all required facts
    "collected_facts": object     // key/value facts you can already extract from the conversation
  }
- The user can ONLY answer questions in chat. They CANNOT upload documents,
  attach files, or share screens. Never ask them to "upload", "attach", or
  "share" anything — only to type or state a fact.
- If you need information that's outside the scope of what the user can
  reasonably provide in chat (e.g. a specific statute text), DO NOT ask
  for it. Instead, set sensible defaults from the user's stated
  jurisdiction and proceed.
- Do NOT invent facts the lawyer has not stated.
- If LEGAL_CONTEXT is present, use it to inform your questions and the
  eventual draft. Do not refer to LEGAL_CONTEXT explicitly to the user
  (they did not provide it — it is retrieved internally).
SYS;

        $promptParts = [];
        if ($template) {
            $promptParts[] = 'TEMPLATE: '.$template->name.($template->category ? ' ('.$template->category.')' : '');
            if ($template->description) {
                $promptParts[] = 'DESCRIPTION: '.$template->description;
            }
            if (! empty($required)) {
                $promptParts[] = 'REQUIRED FIELDS: '.implode(', ', array_keys($required));
            }
        }
        if ($context !== '') {
            $promptParts[] = "LEGAL_CONTEXT (top-K retrieved):\n".$context;
        }
        $promptParts[] = 'Now decide whether to ask questions or signal ready_to_draft. Output JSON only.';

        $messages = $this->buildHistory($session);
        $messages[] = ['role' => 'user', 'content' => implode("\n\n", $promptParts)];

        $response = $this->llm->chatStream($messages, [
            'system' => $system,
            'max_tokens' => 1024,
            'temperature' => 0.2,
        ], $onDelta);

        return $this->finalizeClarifyingResponse($session, $response['content']);
    }

    /**
     * Shared persistence path for streaming + non-streaming clarifying flow.
     */
    private function finalizeClarifyingResponse(ChatSession $session, string $content): ChatMessage
    {
        $parsed = self::parseJson($content);
        $questions = is_array($parsed['questions'] ?? null) ? array_values(array_filter($parsed['questions'], 'is_string')) : [];
        $ready = (bool) ($parsed['ready_to_draft'] ?? false);
        $newFacts = is_array($parsed['collected_facts'] ?? null) ? $parsed['collected_facts'] : [];

        $existing = is_array($session->collected_facts) ? $session->collected_facts : [];
        $session->collected_facts = array_merge($existing, $newFacts);
        $session->open_questions = $questions;
        $session->status = $ready ? 'drafting' : 'clarifying';
        $session->save();

        $assistantText = $ready
            ? 'I have enough information to draft. Click "Generate draft" to produce the contract.'
            : ($questions === []
                ? 'Could you share more details about the parties, dates, payment terms, and governing law?'
                : "I need a few more details before drafting:\n\n".implode("\n", array_map(fn ($q) => '• '.$q, $questions)));

        return ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'assistant',
            'content' => $assistantText,
            'metadata' => [
                'kind' => 'clarifying',
                'questions' => $questions,
                'ready_to_draft' => $ready,
                'collected_facts' => $newFacts,
            ],
        ]);
    }

    private function produceDraft(ChatSession $session): ChatMessage
    {
        $template = $session->template;
        $context = $this->ragContext($session);

        // Pre-fill the template server-side. Smaller open models (Llama 8b)
        // can't reliably do `{{placeholder}}` → fact substitution from
        // conversation history. Doing it ourselves makes the second LLM
        // call much smaller AND guarantees correctness regardless of model.
        $prefilledTemplateBody = null;
        if ($template) {
            $facts = is_array($session->collected_facts) ? $session->collected_facts : [];
            $requiredKeys = is_array($template->required_fields) ? array_keys($template->required_fields) : [];
            $intent = $session->messages()->where('role', 'user')->orderBy('id')->first()?->content ?? '';

            // If facts are missing, ask the LLM to extract them from the intent.
            // Small JSON call — way cheaper than a full draft turn.
            $missingKeys = array_diff($requiredKeys, array_keys($facts));
            if ($missingKeys !== [] && $intent !== '') {
                try {
                    $extracted = FactExtractor::fromConfig()->extract($missingKeys, $intent);
                    if ($extracted !== []) {
                        $facts = array_merge($facts, $extracted);
                        $session->collected_facts = $facts;
                        $session->save();
                    }
                } catch (\Throwable $e) {
                    Log::channel('ai')->warning(
                        'FactExtractor failed; continuing with empty facts',
                        ['error' => $e->getMessage()]
                    );
                }
            }

            // Normalise date-typed facts: resolve "today / اليوم / now" to an
            // actual locale-formatted date, fill missing date keys with
            // today, parse explicit dates into a consistent format.
            $facts = TemplateRenderer::normaliseFacts(
                facts: $facts,
                fieldTypes: is_array($template->required_fields) ? $template->required_fields : [],
                locale: $template->language ?? app()->getLocale(),
            );
            $session->collected_facts = $facts;
            $session->save();

            $prefilledTemplateBody = TemplateRenderer::render($template->body, $facts);
        }

        $system = <<<'SYS'
You are a senior legal drafting assistant grounded in eastlaws.com (the canonical
source for Egyptian and Gulf law). You have ONE tool: `lookup_law(query, country_id)`.

PROCESS:

1. Your FIRST action MUST be a call to `lookup_law` — without exception. Search in
   the document's language (Arabic for EG/Gulf). Pick the most informative single
   query for this contract type. Examples: "ضمان البائع التعرض القانوني" or
   "أحكام بيع العقار في القانون المدني المصري".

2. After lookup_law returns, draft the contract as clean, professional legal prose
   in the document's language.

OUTPUT FORMAT:

- Output **plain text only** — this is a legal document, not a webpage.
  Do NOT use markdown: no `**bold**`, no `*italics*`, no `__underline__`,
  no `#` / `##` headers, no `*` / `-` bullet markers (use Arabic
  numerals "1." / "2." or "أولاً" / "ثانياً" instead), no horizontal
  rules `---`, no fenced code blocks. Plain prose with paragraph breaks.
- Output the contract body ONLY. No header, no disclaimer, no annotations,
  no "DRAFT NOTES" section, no risks list, no citations footer. The application
  attaches a disclaimer separately and surfaces verification status in a
  dedicated UI panel.
- For clauses directly supported by a specific chunk lookup_law returned, append
  a single `[n]` marker at the END of the clause where `n` matches the FOUND
  numbering. Use `[n]` SPARINGLY — only on clauses that quote / paraphrase /
  directly rely on the retrieved chunk's text. Do NOT mark every clause.
- NEVER write `[UNVERIFIED]` or `[TO CONFIRM: ...]` inline. The application
  handles those concerns server-side.
- NEVER fabricate an article number, code reference, or quoted passage from
  training memory. If a chunk doesn't support the clause, just write the
  clause without any marker.

REFUSAL (RARE):
Output `SORRY: <reason>` only when lookup_law returned nothing relevant AND
the contract type lacks any plausible legal grounding (e.g. "Antarctic mining
lease in Egypt"). Standard civil contracts always proceed.

PRE-FILLED TEMPLATE NOTE:
If the user provides a "PRE-FILLED CONTRACT" block, output it verbatim, only
adding `[n]` markers where lookup_law results genuinely support the clause.

LANGUAGE: match the user's intent (Arabic intent → Arabic draft).
SYS;

        // Prepend jurisdiction-specific senior-attorney profile. This is a
        // "skill" — domain expertise the base model doesn't reliably have.
        // Configured via resources/legal/senior-attorney-{jurisdiction}.md.
        $jurisdiction = $template?->jurisdiction;
        $profile = AttorneyProfile::for($jurisdiction);
        if ($profile !== '') {
            $system = $profile."\n\n---\n\n".$system;
        }

        $facts = is_array($session->collected_facts) ? $session->collected_facts : [];

        $promptParts = [];
        if ($prefilledTemplateBody !== null) {
            // Template already substituted server-side. Tell the model so it
            // doesn't try to fill placeholders itself, and instruct it to
            // ONLY add citations + draft notes.
            $promptParts[] = "PRE-FILLED CONTRACT (placeholders already substituted; do NOT modify the body except to ADD [n] citations inline and append --- DRAFT NOTES ---):\n".$prefilledTemplateBody;
        }
        if ($context !== '') {
            $promptParts[] = "LEGAL_CONTEXT (top-K retrieved):\n".$context;
        }
        if ($prefilledTemplateBody !== null) {
            $promptParts[] = "Output the contract verbatim with [n] citation markers added inline where supported by LEGAL_CONTEXT, then '--- DRAFT NOTES ---' with Risks and Citations bullets.";
        } else {
            $promptParts[] = 'Produce the full contract now.';
        }

        $messages = $this->buildHistory($session);
        $messages[] = ['role' => 'user', 'content' => implode("\n\n", $promptParts)];

        $toolChunkIds = [];
        $response = $this->runWithTools($messages, [
            'system' => $system,
            'max_tokens' => (int) config('lawyer.draft_max_tokens', 4096),
            'temperature' => 0.3,
            'tools' => [LawLookupTool::definition()],
            // Force the model to call lookup_law on the FIRST turn. Without
            // this, smaller models (Llama 3.1 8b) will skip the tool entirely
            // when handed a pre-filled template and just hallucinate citations
            // from training memory. Forcing the tool gives us actual chunks
            // to verify against.
            'tool_choice' => ['type' => 'tool', 'name' => 'lookup_law'],
        ], $toolChunkIds);

        $body = $response['content'];

        // Hallucination guard: if the model emitted [n] citation markers but
        // no chunks were ever surfaced via lookup_law, those markers are
        // fabricated from training memory. Replace them with [UNVERIFIED] so
        // the lawyer reviewing the draft sees the real grounding state.
        if ($toolChunkIds === [] && preg_match('/\[\d+\]/u', $body)) {
            $body = preg_replace('/\[\d+\]/u', '[UNVERIFIED]', $body) ?? $body;
        }

        // Refusal: AI couldn't find authority on eastlaws and refused to fabricate.
        if (self::isRefusal($body)) {
            $session->status = 'refused';
            $session->save();

            app(AuditLogger::class)->log(
                action: 'session.refused',
                subject: $session,
                summary: 'Drafting refused — no eastlaws authority',
                metadata: ['tool_chunks_seen' => $toolChunkIds],
                userId: $session->user_id,
            );

            return ChatMessage::create([
                'chat_session_id' => $session->id,
                'role' => 'assistant',
                'content' => $body !== '' ? $body : 'SORRY: I cannot draft this because eastlaws has no authority for the requested topic.',
                'metadata' => ['kind' => 'refusal'],
            ]);
        }

        // Empty-response guard: if the model returned almost nothing (<100
        // chars), don't pretend it's a draft — surface a clear error so the
        // user knows to retry rather than a contract with just the disclaimer
        // appended. Common causes: token budget too low, rate-limit truncation,
        // model confused by malformed history.
        if (mb_strlen(trim($body)) < 100) {
            $session->status = 'failed';
            $session->save();

            return ChatMessage::create([
                'chat_session_id' => $session->id,
                'role' => 'assistant',
                'content' => __('The model returned an empty response. This usually means token budget was too tight or the request hit a rate limit. Try again in a minute, or reduce LAWYER_DRAFT_MAX_TOKENS / LAWYER_TOP_K.'),
                'metadata' => ['kind' => 'error', 'raw_length' => mb_strlen($body)],
            ]);
        }

        $disclaimer = (string) config('lawyer.disclaimer', '');
        if ($disclaimer !== '') {
            $body .= "\n\n---\n".$disclaimer;
        }

        // Citation audit: verify every [n] marker against chunks the model
        // actually saw via lookup_law. Stored on the contract for the UI.
        $audit = $this->verifier->audit($body, $toolChunkIds);

        // Body cleanup pass — strips internal annotations so the contract
        // reads like a real legal document, not an annotated diagnostic:
        //   1. Remove `[n]` markers the verifier rejected (no fake grounding)
        //   2. Remove any stray `[UNVERIFIED]` the model emitted anyway
        //   3. Replace `[TO CONFIRM: key]` with a fill-in blank line
        //   4. Strip any "--- DRAFT NOTES ---" footer the model added
        //      (the system prompt now forbids it; this is a defensive
        //       backstop for older sessions or model lapses)
        // Verification status remains queryable via $contract->citation_audit
        // for the UI's audit panel — only the body itself is sanitised.
        $body = $this->cleanBodyForReading($body, $audit);

        $citationIds = self::extractCitations($body);

        // Corpus-citation gate — second-pass regex verifier that confirms
        // every "Article N", "Law M of YYYY", "المادة ١٤٨", "القانون رقم …"
        // citation in the cleaned body actually exists in the indexed
        // statutory corpus for the chosen jurisdiction. Unverified
        // citations downgrade the contract status to needs_review (rather
        // than being hard-refused) so the lawyer keeps the work product
        // and can review what's flagged.
        $jurisdiction = $this->resolveJurisdiction(
            $session,
            $body,
            is_array($session->collected_facts) ? $session->collected_facts : [],
        );
        $corpusReport = $this->corpusGate->verify($body, $jurisdiction);
        $contractStatus = $corpusReport->passes() ? 'draft' : 'needs_review';

        $contract = Contract::create([
            'user_id' => $session->user_id,
            'chat_session_id' => $session->id,
            'contract_template_id' => $session->contract_template_id,
            'title' => $session->title ?? 'Untitled contract',
            'parties' => $facts['parties'] ?? null,
            'variables' => $facts,
            'citations' => $citationIds,
            'citation_audit' => $audit,
            'corpus_gate' => [
                'passes' => $corpusReport->passes(),
                'confidence_pct' => $corpusReport->confidencePercent(),
                'verified' => $corpusReport->verifiedCount(),
                'uncertain' => $corpusReport->uncertainCount(),
                'unverified' => $corpusReport->unverifiedCount(),
                'unverified_citations' => $corpusReport->unverifiedCitations(),
                'uncertain_citations' => $corpusReport->uncertainCitations(),
            ],
            'body' => $body,
            'status' => $contractStatus,
            'version' => 1,
        ]);

        $session->status = 'drafted';
        $session->save();

        app(AuditLogger::class)->log(
            action: 'contract.created',
            subject: $contract,
            summary: 'Draft generated · '.$corpusReport->summary(),
            metadata: [
                'session_id' => $session->id,
                'citation_summary' => $audit['summary'] ?? null,
                'corpus_gate_summary' => $corpusReport->summary(),
                'corpus_gate_passes' => $corpusReport->passes(),
                'corpus_gate_confidence' => $corpusReport->confidencePercent(),
                'tool_chunks_seen' => $toolChunkIds,
                'body_length' => mb_strlen($body),
                'jurisdiction' => $jurisdiction,
            ],
            userId: $session->user_id,
        );

        return ChatMessage::create([
            'chat_session_id' => $session->id,
            'role' => 'assistant',
            'content' => $body,
            'metadata' => [
                'kind' => 'draft',
                'citations' => $citationIds,
                'citation_summary' => $audit['summary'] ?? null,
            ],
        ]);
    }

    /**
     * Final cleanup pass for the contract body before it's stored. Removes
     * every internal annotation so the document reads as professional legal
     * prose. Verification status survives on $contract->citation_audit for
     * the UI; the body itself is for the lawyer + their client.
     *
     *   - Verified `[n]`        → kept (real grounding)
     *   - Unverified `[n]`      → removed (the marker was a model overreach)
     *   - Stray `[UNVERIFIED]`  → removed
     *   - `[TO CONFIRM: key]`   → replaced with `__________` blank
     *   - `--- DRAFT NOTES ---` footer → stripped entirely
     */
    private function cleanBodyForReading(string $body, array $audit): string
    {
        // 1. Walk `[n]` markers in document order, dropping any whose verdict
        //    isn't `verified` (along with leading whitespace so we don't leave
        //    awkward double-spaces or stranded punctuation).
        $markerRows = is_array($audit['markers'] ?? null) ? array_values($audit['markers']) : [];
        $i = 0;
        $body = preg_replace_callback(
            '/\s*\[(\d+)\]/u',
            function ($matches) use (&$i, $markerRows) {
                $row = $markerRows[$i] ?? null;
                $i++;
                $verdict = is_array($row) ? (string) ($row['verdict'] ?? 'unverified') : 'unverified';

                return $verdict === 'verified' ? $matches[0] : '';
            },
            $body
        ) ?? $body;

        // 2. Strip any literal `[UNVERIFIED]` the model emitted directly.
        $body = preg_replace('/\s*\[UNVERIFIED\]/u', '', $body) ?? $body;

        // 3. Replace `[TO CONFIRM: key]` with a printable blank line —
        //    a familiar fill-in-later affordance, not a developer flag.
        $body = preg_replace('/\[TO CONFIRM:\s*[^\]]+\]/u', '____________________', $body) ?? $body;

        // 4. Strip the entire `--- DRAFT NOTES ---` footer if present.
        //    Greedy to end-of-string so any trailing risks/citations bullets
        //    go with it. Per-clause verdicts and risks live in the audit
        //    panel; they don't belong in the contract body.
        $body = preg_replace('/\s*\n\s*---\s*DRAFT NOTES\s*---.*$/su', '', $body) ?? $body;

        // 5. Strip markdown formatting characters. Most LLMs default to
        //    markdown for "structure"; legal documents are plain prose.
        //    Remove inline emphasis, headings, horizontal rules, and
        //    fenced code blocks. List markers (1. / -) are kept intact.
        $body = preg_replace('/\*\*(.+?)\*\*/us', '$1', $body) ?? $body;       // **bold** → bold
        $body = preg_replace('/__(.+?)__/us', '$1', $body) ?? $body;           // __bold__
        $body = preg_replace('/(?<!\w)\*([^\*\n]+?)\*(?!\w)/u', '$1', $body) ?? $body; // *italic*
        $body = preg_replace('/(?<!\w)_([^_\n]+?)_(?!\w)/u', '$1', $body) ?? $body;    // _italic_
        $body = preg_replace('/^\s*#{1,6}\s+/mu', '', $body) ?? $body;         // # / ## headers
        $body = preg_replace('/^\s*[-*]\s+/mu', '', $body) ?? $body;           // - / * bullets at start of line
        $body = preg_replace('/^\s*-{3,}\s*$/mu', '', $body) ?? $body;         // --- horizontal rules
        $body = preg_replace('/`([^`]+)`/u', '$1', $body) ?? $body;            // `code`

        // 6. Tidy whitespace — collapse runs of blank lines, trim trailing.
        $body = preg_replace('/\n{3,}/u', "\n\n", $body) ?? $body;

        return rtrim($body);
    }

    public static function isRefusal(string $body): bool
    {
        $trimmed = ltrim($body);
        if (str_starts_with($trimmed, 'SORRY:')) {
            return true;
        }
        // Defensive: sometimes Claude phrases refusal slightly differently.
        if (mb_strlen($trimmed) < 600 && preg_match('/eastlaws[^\n]{0,80}(no authority|not found|no matching)/iu', $trimmed)) {
            return true;
        }

        return false;
    }

    /**
     * Run an LLM call that may invoke tools. Loops until the model stops
     * requesting tools or maxToolRounds is reached. Returns the final
     * response (with `content` populated from the last text turn).
     *
     * Accumulates every chunk ID surfaced by lookup_law into $toolChunkIds
     * (passed by reference) so the post-draft CitationVerifier can audit
     * markers against what the model actually saw.
     *
     * @param  array<int, array{role:string, content:mixed}>  $messages
     * @param  array<string, mixed>  $options
     * @param  array<int, int>  $toolChunkIds  out-param: chunk IDs surfaced during the loop
     * @return array{content:string, raw:array<string,mixed>, stop_reason:?string, tool_calls:array<int,array<string,mixed>>}
     */
    private function runWithTools(array $messages, array $options, array &$toolChunkIds = []): array
    {
        $rounds = 0;
        // tool_choice (force a tool call) applies ONLY to the first turn —
        // after that we let the model decide whether to keep searching or
        // produce the draft. Forcing it on every turn would loop infinitely.
        $forcedChoice = $options['tool_choice'] ?? null;
        $response = $this->llm->chat($messages, $options);

        $followUpOptions = $options;
        unset($followUpOptions['tool_choice']);

        while (
            $rounds < $this->maxToolRounds
            && ($response['stop_reason'] ?? null) === 'tool_use'
            && ! empty($response['tool_calls'])
        ) {
            $rounds++;

            // Append assistant turn with the original content blocks (text + tool_use).
            $messages[] = [
                'role' => 'assistant',
                'content' => $response['raw']['content'] ?? [],
            ];

            // Execute each requested tool and assemble tool_result blocks.
            $toolResults = [];
            foreach ($response['tool_calls'] as $call) {
                $name = (string) ($call['name'] ?? '');
                $input = is_array($call['input'] ?? null) ? $call['input'] : [];
                $output = match ($name) {
                    'lookup_law' => $this->lookup->execute($input),
                    default => "Unknown tool: {$name}",
                };
                if ($name === 'lookup_law') {
                    foreach ($this->lookup->lastSeenChunkIds() as $cid) {
                        if (! in_array($cid, $toolChunkIds, true)) {
                            $toolChunkIds[] = $cid;
                        }
                    }
                }
                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => (string) ($call['id'] ?? ''),
                    'content' => $output,
                ];
            }
            $messages[] = ['role' => 'user', 'content' => $toolResults];

            // Subsequent turns: let the model decide whether to call again
            // or finalise. tool_choice removed from options.
            $response = $this->llm->chat($messages, $followUpOptions);
        }

        return $response;
    }

    private function factsAppearComplete(ChatSession $session): bool
    {
        $template = $session->template;
        $required = is_array($template?->required_fields) ? array_keys($template->required_fields) : [];
        if ($required === []) {
            return false;
        }
        $facts = is_array($session->collected_facts) ? $session->collected_facts : [];
        foreach ($required as $key) {
            if (! array_key_exists($key, $facts) || $facts[$key] === '' || $facts[$key] === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public static function parseJson(string $text): array
    {
        $trimmed = trim($text);
        // Strip markdown fences if present.
        $trimmed = preg_replace('/^```(?:json)?\s*|\s*```$/u', '', $trimmed) ?? $trimmed;
        $start = strpos($trimmed, '{');
        $end = strrpos($trimmed, '}');
        if ($start === false || $end === false || $end <= $start) {
            return [];
        }
        $candidate = substr($trimmed, $start, $end - $start + 1);
        $decoded = json_decode($candidate, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<int, int>
     */
    public static function extractCitations(string $body): array
    {
        if (preg_match_all('/\[(\d+)\]/u', $body, $m)) {
            $ids = array_map('intval', array_unique($m[1]));
            sort($ids);

            return array_values($ids);
        }

        return [];
    }
}
