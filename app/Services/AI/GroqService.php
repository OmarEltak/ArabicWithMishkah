<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Adapter for Groq's OpenAI-compatible chat completions API.
 *
 *   POST {base}/chat/completions
 *
 * The request and response shapes are 1:1 with OpenAI. We translate to/from
 * Anthropic's `tool_use`/`tool_result` semantics so the rest of the codebase
 * (ContractDraftingService, LawLookupTool) doesn't need a vendor branch.
 *
 * Translation rules:
 *   - Anthropic tool definition `input_schema` → OpenAI tool `function.parameters`
 *   - Anthropic assistant content blocks (text + tool_use) → OpenAI assistant
 *     message with `content` (text) + `tool_calls` array
 *   - Anthropic tool_result block → OpenAI message role=`tool` with the same
 *     `tool_call_id`
 *
 * Quality note: Groq's hosted models (Llama 3.3 70B, Mixtral) handle Arabic
 * and tool calls reasonably but trail Claude on legal-domain phrasing. Set
 * LLM_PROVIDER=anthropic in .env when quality matters more than cost.
 */
class GroqService implements LlmInterface
{
    /**
     * Index of the key currently preferred. Sticky: when a key succeeds we
     * keep using it; when it 429s we rotate forward. Persisted in cache so
     * subsequent requests in the same minute don't keep slamming the
     * already-rate-limited key.
     */
    private const ACTIVE_KEY_CACHE = 'groq.active_key_index';

    public function __construct(
        /** @var array<int, string> */
        private readonly array $apiKeys,
        private readonly string $model,
        private readonly string $baseUrl,
        private readonly ?UsageTracker $tracker = null,
    ) {}

    public static function fromConfig(): self
    {
        $keys = config('services.groq.api_keys', []);
        if (! is_array($keys) || $keys === []) {
            // Fallback to the legacy single-key config for older .env files.
            $single = (string) (config('services.groq.api_key') ?? '');
            $keys = $single !== '' ? [$single] : [];
        }

        return new self(
            apiKeys: array_values(array_filter(array_map('strval', $keys))),
            model: (string) config('services.groq.model', 'llama-3.3-70b-versatile'),
            baseUrl: rtrim((string) config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/'),
            tracker: app(UsageTracker::class),
        );
    }

    public function isConfigured(): bool
    {
        return $this->apiKeys !== [];
    }

    /**
     * Where in the key pool to start trying for the next request. Reads from
     * the array cache so a 429-rotated index sticks within the same minute.
     */
    private function currentKeyIndex(): int
    {
        if (count($this->apiKeys) <= 1) {
            return 0;
        }
        $stored = Cache::driver('array')->get(self::ACTIVE_KEY_CACHE, 0);

        return is_int($stored) ? max(0, min(count($this->apiKeys) - 1, $stored)) : 0;
    }

    private function rememberKeyIndex(int $index): void
    {
        if (count($this->apiKeys) <= 1) {
            return;
        }
        Cache::driver('array')->put(
            self::ACTIVE_KEY_CACHE,
            $index % count($this->apiKeys),
            300
        );
    }

    public function chat(array $messages, array $options = []): array
    {
        if (! $this->isConfigured()) {
            return $this->mockResponse($messages, $options);
        }

        $this->tracker?->assertWithinBudget();

        $payload = $this->buildPayload($messages, $options, stream: false);
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);

        // Try keys in pool order, starting from the sticky-cached index.
        // 429 → rotate to next key. Auth failures (401/403) → also rotate
        // (a revoked/wrong key shouldn't block the rest). Other errors →
        // bubble immediately (no point retrying a 400 schema error).
        $keyCount = count($this->apiKeys);
        $start = $this->currentKeyIndex();
        $lastFailureStatus = 0;
        $lastFailureBody = '';

        for ($attempt = 0; $attempt < $keyCount; $attempt++) {
            $index = ($start + $attempt) % $keyCount;
            $key = $this->apiKeys[$index];

            Log::channel('ai')->info('Groq request', [
                'bytes' => strlen($payloadJson),
                'message_count' => count($payload['messages'] ?? []),
                'tool_count' => count($payload['tools'] ?? []),
                'key_index' => $index,
                'attempt' => $attempt + 1,
                'pool_size' => $keyCount,
            ]);

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$key,
                    'Content-Type' => 'application/json',
                ])
                    ->timeout(120)
                    ->post($this->baseUrl.'/chat/completions', $payload);
            } catch (ConnectionException $e) {
                Log::channel('ai')->warning('Groq connection failed', [
                    'error' => $e->getMessage(),
                    'key_index' => $index,
                ]);
                throw new RuntimeException('AI service is unreachable. Please try again.', 0, $e);
            }

            if ($response->successful()) {
                $this->rememberKeyIndex($index);
                $data = $response->json();
                $usage = is_array($data['usage'] ?? null) ? $data['usage'] : [];
                $this->tracker?->record(
                    provider: 'groq',
                    operation: 'chat',
                    model: $this->model,
                    inputTokens: (int) ($usage['prompt_tokens'] ?? 0),
                    outputTokens: (int) ($usage['completion_tokens'] ?? 0),
                    metadata: [
                        'stop_reason' => $data['choices'][0]['finish_reason'] ?? null,
                        'key_index' => $index,
                    ],
                );

                return $this->normalize(is_array($data) ? $data : []);
            }

            $status = $response->status();
            $lastFailureStatus = $status;
            $lastFailureBody = mb_substr($response->body(), 0, 800);

            // Only rotate on transient/auth failures. 4xx-other (400 schema,
            // 404, etc.) reflect the request itself — retrying with another
            // key won't help.
            $shouldRotate = in_array($status, [429, 401, 403], true);

            Log::channel('ai')->warning('Groq API error', [
                'status' => $status,
                'body' => $lastFailureBody,
                'key_index' => $index,
                'will_rotate' => $shouldRotate,
            ]);

            if (! $shouldRotate) {
                break;
            }

            // Advance the sticky pointer so subsequent requests prefer the
            // next key without re-tripping this one.
            $this->rememberKeyIndex(($index + 1) % $keyCount);
        }

        // All keys exhausted (or non-rotatable error). Record + raise.
        $this->tracker?->record(
            provider: 'groq',
            operation: 'chat',
            model: $this->model,
            inputTokens: 0,
            outputTokens: 0,
            status: 'error',
            errorMessage: 'HTTP '.$lastFailureStatus.' after '.$keyCount.' key(s)',
        );

        throw new RuntimeException('AI request failed: '.$lastFailureStatus);
    }

    public function chatStream(array $messages, array $options, callable $onDelta): array
    {
        // Groq supports SSE streaming, but the OpenAI-compatible streaming
        // protocol (delta accumulation, tool-call argument streaming) is a
        // larger change than this MVP needs. For now, emit a single delta.
        $response = $this->chat($messages, $options);
        if ($response['content'] !== '') {
            $onDelta($response['content']);
        }

        return $response;
    }

    /**
     * Build an OpenAI-compatible payload from our Anthropic-shaped inputs.
     *
     * @param  array<int, array{role:string, content:string|array}>  $messages
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function buildPayload(array $messages, array $options, bool $stream): array
    {
        $oaiMessages = [];
        if (! empty($options['system'])) {
            $oaiMessages[] = ['role' => 'system', 'content' => (string) $options['system']];
        }
        foreach ($messages as $msg) {
            $oaiMessages = array_merge($oaiMessages, $this->translateMessage($msg));
        }

        $payload = [
            'model' => $this->model,
            'messages' => $oaiMessages,
            'max_tokens' => $options['max_tokens'] ?? 4096,
        ];
        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }
        if (! empty($options['tools'])) {
            $payload['tools'] = $this->translateTools($options['tools']);
        }
        if (isset($options['tool_choice'])) {
            $payload['tool_choice'] = $this->translateToolChoice($options['tool_choice']);
        }
        if ($stream) {
            $payload['stream'] = true;
        }

        return $payload;
    }

    /**
     * Translate Anthropic-style tool_choice to OpenAI-compatible form.
     *
     *   ['type' => 'auto']                       → 'auto'
     *   ['type' => 'any']                        → 'required'
     *   ['type' => 'tool', 'name' => 'foo']      → ['type' => 'function', 'function' => ['name' => 'foo']]
     *
     * @param  array<string, mixed>|string  $choice
     * @return array<string, mixed>|string
     */
    private function translateToolChoice(array|string $choice): array|string
    {
        if (is_string($choice)) {
            return $choice; // already an OpenAI-style string ('auto' / 'required' / 'none')
        }
        $type = (string) ($choice['type'] ?? 'auto');

        return match ($type) {
            'any' => 'required',
            'tool' => [
                'type' => 'function',
                'function' => ['name' => (string) ($choice['name'] ?? '')],
            ],
            default => 'auto',
        };
    }

    /**
     * Translate one Anthropic-shaped message into one or more OpenAI messages.
     *
     * @param  array{role:string, content:string|array}  $msg
     * @return array<int, array<string, mixed>>
     */
    private function translateMessage(array $msg): array
    {
        $role = (string) ($msg['role'] ?? 'user');
        $content = $msg['content'];

        if (is_string($content)) {
            return [['role' => $role, 'content' => $content]];
        }

        if (! is_array($content)) {
            return [];
        }

        if ($role === 'assistant') {
            // Assistant content is a list of blocks: text + tool_use.
            $text = '';
            $toolCalls = [];
            foreach ($content as $block) {
                if (! is_array($block)) {
                    continue;
                }
                if (($block['type'] ?? null) === 'text') {
                    $text .= (string) ($block['text'] ?? '');
                } elseif (($block['type'] ?? null) === 'tool_use') {
                    $toolCalls[] = [
                        'id' => (string) ($block['id'] ?? ''),
                        'type' => 'function',
                        'function' => [
                            'name' => (string) ($block['name'] ?? ''),
                            'arguments' => json_encode($block['input'] ?? new \stdClass, JSON_UNESCAPED_UNICODE),
                        ],
                    ];
                }
            }
            $out = ['role' => 'assistant', 'content' => $text !== '' ? $text : null];
            if ($toolCalls !== []) {
                $out['tool_calls'] = $toolCalls;
            }

            return [$out];
        }

        if ($role === 'user') {
            // User content can be plain text OR a list of tool_result blocks.
            // Anthropic packs all tool_results into one user message; OpenAI
            // requires one message per tool_call_id with role=tool. Split.
            $messages = [];
            $textParts = [];
            foreach ($content as $block) {
                if (! is_array($block)) {
                    continue;
                }
                if (($block['type'] ?? null) === 'tool_result') {
                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => (string) ($block['tool_use_id'] ?? ''),
                        'content' => (string) ($block['content'] ?? ''),
                    ];
                } elseif (($block['type'] ?? null) === 'text') {
                    $textParts[] = (string) ($block['text'] ?? '');
                }
            }
            if ($textParts !== []) {
                $messages[] = ['role' => 'user', 'content' => implode("\n", $textParts)];
            }

            return $messages;
        }

        return [];
    }

    /**
     * Translate Anthropic tool definitions to OpenAI format.
     *
     * @param  array<int, array<string, mixed>>  $tools
     * @return array<int, array<string, mixed>>
     */
    private function translateTools(array $tools): array
    {
        $out = [];
        foreach ($tools as $tool) {
            if (! is_array($tool)) {
                continue;
            }
            $out[] = [
                'type' => 'function',
                'function' => [
                    'name' => (string) ($tool['name'] ?? ''),
                    'description' => (string) ($tool['description'] ?? ''),
                    'parameters' => $tool['input_schema'] ?? ['type' => 'object', 'properties' => new \stdClass],
                ],
            ];
        }

        return $out;
    }

    /**
     * Normalise an OpenAI chat completion response into the shape we use
     * everywhere else (mirrors AnthropicService::normalizeResponse).
     *
     * @param  array<string, mixed>  $data
     * @return array{content:string, raw:array<string,mixed>, stop_reason:?string, tool_calls:array<int,array<string,mixed>>}
     */
    private function normalize(array $data): array
    {
        $choice = $data['choices'][0] ?? [];
        $message = is_array($choice['message'] ?? null) ? $choice['message'] : [];
        $finish = (string) ($choice['finish_reason'] ?? 'stop');

        $text = (string) ($message['content'] ?? '');
        $toolCalls = [];
        foreach ((array) ($message['tool_calls'] ?? []) as $call) {
            if (! is_array($call)) {
                continue;
            }
            $args = (string) ($call['function']['arguments'] ?? '{}');
            $decoded = json_decode($args, true);
            $toolCalls[] = [
                'id' => (string) ($call['id'] ?? ''),
                'name' => (string) ($call['function']['name'] ?? ''),
                'input' => is_array($decoded) ? $decoded : [],
            ];
        }

        // Translate finish_reason → Anthropic-style stop_reason so the tool-use
        // loop in ContractDraftingService keeps working unchanged.
        $stopReason = match ($finish) {
            'tool_calls' => 'tool_use',
            'length' => 'max_tokens',
            'stop' => 'end_turn',
            default => $finish,
        };

        // Synthesize Anthropic-style content blocks for the assistant turn so
        // ContractDraftingService::runWithTools can append `raw.content` to
        // history without further translation.
        $assistantBlocks = [];
        if ($text !== '') {
            $assistantBlocks[] = ['type' => 'text', 'text' => $text];
        }
        foreach ($toolCalls as $call) {
            $assistantBlocks[] = [
                'type' => 'tool_use',
                'id' => $call['id'],
                'name' => $call['name'],
                'input' => $call['input'],
            ];
        }

        return [
            'content' => trim($text),
            'raw' => array_merge($data, ['content' => $assistantBlocks]),
            'stop_reason' => $stopReason,
            'tool_calls' => $toolCalls,
        ];
    }

    /**
     * @param  array<int, array{role:string, content:string|array}>  $messages
     * @param  array<string, mixed>  $options
     * @return array{content:string, raw:array<string,mixed>, stop_reason:?string, tool_calls:array<int,array<string,mixed>>}
     */
    private function mockResponse(array $messages, array $options): array
    {
        $lastUser = '';
        foreach (array_reverse($messages) as $msg) {
            if (($msg['role'] ?? null) === 'user') {
                $lastUser = is_string($msg['content']) ? $msg['content'] : json_encode($msg['content']);
                break;
            }
        }
        $system = (string) ($options['system'] ?? '');
        $sig = strtolower($system.' '.$lastUser);
        if (str_contains($sig, 'json') && str_contains($sig, 'questions')) {
            return [
                'content' => json_encode([
                    'questions' => ['What are the parties?', 'What is the date?', 'What jurisdiction?'],
                    'ready_to_draft' => false,
                ]),
                'raw' => ['mock' => true],
                'stop_reason' => 'end_turn',
                'tool_calls' => [],
            ];
        }

        return [
            'content' => '[MOCK GROQ DRAFT] Set GROQ_API_KEY in .env for real responses. '.mb_substr($lastUser, 0, 200),
            'raw' => ['mock' => true],
            'stop_reason' => 'end_turn',
            'tool_calls' => [],
        ];
    }
}
