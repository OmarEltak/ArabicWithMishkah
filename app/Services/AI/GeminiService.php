<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Adapter for Google's Gemini API. Implements LlmInterface so the rest of the
 * codebase (ContractDraftingService, LawLookupTool) doesn't need a vendor
 * branch. Translates Anthropic-shaped messages, tools, and tool_choice into
 * Gemini's native protocol.
 *
 *   POST https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent
 *
 * Translation rules:
 *  - Anthropic role 'assistant' ↔ Gemini role 'model'
 *  - Anthropic 'system' option ↔ Gemini systemInstruction (separate top-level)
 *  - Anthropic tool_use content blocks ↔ Gemini functionCall parts
 *  - Anthropic tool_result content blocks (in user message) ↔ Gemini
 *    functionResponse parts
 *  - Anthropic tool_choice {type:'tool', name:'X'} ↔ Gemini toolConfig
 *    {functionCallingConfig: {mode: ANY, allowedFunctionNames: [X]}}
 *
 * Free tier: Gemini 2.0 Flash gives ~1M TPM and 1500 RPD — way more headroom
 * than Groq's free tier (12K TPM / 100K TPD). For a low-volume MVP this is
 * effectively unlimited.
 */
class GeminiService implements LlmInterface
{
    /**
     * Sticky-pointer cache key. Once a key serves a request successfully we
     * keep using it; when it 429s or 403s we rotate the pointer forward and
     * persist for 5 minutes so the next request doesn't re-trip the same key.
     */
    private const ACTIVE_KEY_CACHE = 'gemini.active_key_index';

    public function __construct(
        /** @var array<int, string> */
        private readonly array $apiKeys,
        private readonly string $model,
        private readonly string $baseUrl,
        private readonly ?UsageTracker $tracker = null,
    ) {}

    public static function fromConfig(): self
    {
        $keys = config('services.gemini.api_keys', []);
        if (! is_array($keys) || $keys === []) {
            // Fallback to the legacy single-key config for older .env files.
            $single = (string) (config('services.gemini.api_key') ?? '');
            $keys = $single !== '' ? [$single] : [];
        }

        return new self(
            apiKeys: array_values(array_filter(array_map('strval', $keys))),
            model: (string) config('services.gemini.model', 'gemini-2.0-flash'),
            baseUrl: rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/'),
            tracker: app(UsageTracker::class),
        );
    }

    public function isConfigured(): bool
    {
        return $this->apiKeys !== [];
    }

    private function currentKeyIndex(): int
    {
        if (count($this->apiKeys) <= 1) {
            return 0;
        }
        $stored = \Illuminate\Support\Facades\Cache::driver('array')->get(self::ACTIVE_KEY_CACHE, 0);

        return is_int($stored) ? max(0, min(count($this->apiKeys) - 1, $stored)) : 0;
    }

    private function rememberKeyIndex(int $index): void
    {
        if (count($this->apiKeys) <= 1) {
            return;
        }
        \Illuminate\Support\Facades\Cache::driver('array')->put(
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

        $payload = $this->buildPayload($messages, $options);
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $url = $this->baseUrl.'/models/'.$this->model.':generateContent';

        // Try keys in pool order, starting from the sticky-cached index.
        // 429 (rate limit) → rotate. 401/403 (auth/quota) → rotate. Other
        // errors (400 schema, 500) → bubble immediately; another key won't
        // help.
        $keyCount = count($this->apiKeys);
        $start = $this->currentKeyIndex();
        $lastFailureStatus = 0;
        $lastFailureBody = '';

        for ($attempt = 0; $attempt < $keyCount; $attempt++) {
            $index = ($start + $attempt) % $keyCount;
            $key = $this->apiKeys[$index];

            Log::channel('ai')->info('Gemini request', [
                'bytes' => strlen($payloadJson),
                'message_count' => count($payload['contents'] ?? []),
                'tool_count' => count($payload['tools'][0]['functionDeclarations'] ?? []),
                'key_index' => $index,
                'attempt' => $attempt + 1,
                'pool_size' => $keyCount,
            ]);

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $key,
                ])
                    ->timeout(120)
                    ->post($url, $payload);
            } catch (ConnectionException $e) {
                Log::channel('ai')->warning('Gemini connection failed', [
                    'error' => $e->getMessage(),
                    'key_index' => $index,
                ]);
                throw new RuntimeException('AI service is unreachable. Please try again.', 0, $e);
            }

            if ($response->successful()) {
                $this->rememberKeyIndex($index);
                $data = $response->json();
                $usage = is_array($data['usageMetadata'] ?? null) ? $data['usageMetadata'] : [];
                $this->tracker?->record(
                    provider: 'gemini',
                    operation: 'chat',
                    model: $this->model,
                    inputTokens: (int) ($usage['promptTokenCount'] ?? 0),
                    outputTokens: (int) ($usage['candidatesTokenCount'] ?? 0),
                    metadata: [
                        'finish_reason' => $data['candidates'][0]['finishReason'] ?? null,
                        'key_index' => $index,
                    ],
                );

                return $this->normalize(is_array($data) ? $data : []);
            }

            $status = $response->status();
            $lastFailureStatus = $status;
            $lastFailureBody = mb_substr($response->body(), 0, 800);

            // Rotate on transient/auth/quota failures only. Schema errors
            // (400) and server crashes (5xx) won't be fixed by another key.
            $shouldRotate = in_array($status, [429, 401, 403], true);

            Log::channel('ai')->warning('Gemini API error', [
                'status' => $status,
                'body' => $lastFailureBody,
                'key_index' => $index,
                'will_rotate' => $shouldRotate,
            ]);

            if (! $shouldRotate) {
                break;
            }

            $this->rememberKeyIndex(($index + 1) % $keyCount);
        }

        $this->tracker?->record(
            provider: 'gemini',
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
        // Gemini supports streamGenerateContent but the per-event format
        // differs from Anthropic SSE. For the MVP, fall through to chat() and
        // emit the final content as a single delta — same behaviour as the
        // Groq adapter.
        $response = $this->chat($messages, $options);
        if ($response['content'] !== '') {
            $onDelta($response['content']);
        }

        return $response;
    }

    /**
     * Translate Anthropic-shaped inputs into Gemini's request payload.
     *
     * @param  array<int, array{role:string, content:string|array}>  $messages
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function buildPayload(array $messages, array $options): array
    {
        $contents = [];
        foreach ($messages as $msg) {
            $contents = array_merge($contents, $this->translateMessage($msg));
        }

        $generationConfig = [
            'temperature' => $options['temperature'] ?? 0.3,
            'maxOutputTokens' => $options['max_tokens'] ?? 4096,
        ];

        // Gemini 2.5 Flash defaults to extended thinking, and the thinking
        // tokens count against maxOutputTokens. For deterministic tasks
        // (translation, schema-bound JSON extraction) the caller can pass
        // 'thinking_budget' => 0 to disable it and reclaim the full budget
        // for actual output.
        if (array_key_exists('thinking_budget', $options)) {
            $generationConfig['thinkingConfig'] = [
                'thinkingBudget' => (int) $options['thinking_budget'],
            ];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => $generationConfig,
        ];

        if (! empty($options['system'])) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => (string) $options['system']]],
            ];
        }

        if (! empty($options['tools'])) {
            $payload['tools'] = [[
                'functionDeclarations' => $this->translateTools($options['tools']),
            ]];
        }

        if (isset($options['tool_choice'])) {
            $payload['toolConfig'] = $this->translateToolChoice($options['tool_choice']);
        }

        return $payload;
    }

    /**
     * Translate one Anthropic message into one or more Gemini messages.
     *
     * @param  array{role:string, content:string|array}  $msg
     * @return array<int, array<string, mixed>>
     */
    private function translateMessage(array $msg): array
    {
        $role = (string) ($msg['role'] ?? 'user');
        $content = $msg['content'];
        // Gemini uses 'model' instead of 'assistant'.
        $geminiRole = $role === 'assistant' ? 'model' : 'user';

        if (is_string($content)) {
            return [['role' => $geminiRole, 'parts' => [['text' => $content]]]];
        }

        if (! is_array($content)) {
            return [];
        }

        $parts = [];
        foreach ($content as $block) {
            if (! is_array($block)) {
                continue;
            }
            $type = (string) ($block['type'] ?? '');
            switch ($type) {
                case 'text':
                    $parts[] = ['text' => (string) ($block['text'] ?? '')];
                    break;
                case 'tool_use':
                    // Anthropic assistant tool_use → Gemini functionCall
                    $parts[] = [
                        'functionCall' => [
                            'name' => (string) ($block['name'] ?? ''),
                            'args' => is_array($block['input'] ?? null) ? $block['input'] : new \stdClass,
                        ],
                    ];
                    break;
                case 'tool_result':
                    // Anthropic user tool_result → Gemini functionResponse
                    // Gemini requires the original tool name, but Anthropic
                    // tool_result only carries tool_use_id. We accept that
                    // limitation and synthesize the response wrapper.
                    $resultText = (string) ($block['content'] ?? '');
                    $parts[] = [
                        'functionResponse' => [
                            'name' => (string) ($block['tool_use_id'] ?? 'tool'),
                            'response' => ['content' => $resultText],
                        ],
                    ];
                    break;
            }
        }

        if ($parts === []) {
            return [];
        }

        return [['role' => $geminiRole, 'parts' => $parts]];
    }

    /**
     * Translate Anthropic tool definitions to Gemini's functionDeclarations.
     *
     * @param  array<int, array<string, mixed>>  $tools
     * @return array<int, array<string, mixed>>
     */
    private function translateTools(array $tools): array
    {
        $declarations = [];
        foreach ($tools as $tool) {
            if (! is_array($tool)) {
                continue;
            }
            $declarations[] = [
                'name' => (string) ($tool['name'] ?? ''),
                'description' => (string) ($tool['description'] ?? ''),
                'parameters' => $tool['input_schema'] ?? ['type' => 'object', 'properties' => new \stdClass],
            ];
        }

        return $declarations;
    }

    /**
     * Translate Anthropic tool_choice to Gemini's toolConfig.
     *
     *   ['type' => 'auto']                  → mode: AUTO
     *   ['type' => 'any']                   → mode: ANY
     *   ['type' => 'tool', 'name' => 'foo'] → mode: ANY with allowedFunctionNames: [foo]
     *
     * @param  array<string, mixed>|string  $choice
     * @return array<string, mixed>
     */
    private function translateToolChoice(array|string $choice): array
    {
        if (is_string($choice)) {
            $mode = match ($choice) {
                'required' => 'ANY',
                'none' => 'NONE',
                default => 'AUTO',
            };

            return ['functionCallingConfig' => ['mode' => $mode]];
        }

        $type = (string) ($choice['type'] ?? 'auto');
        if ($type === 'tool' && isset($choice['name'])) {
            return [
                'functionCallingConfig' => [
                    'mode' => 'ANY',
                    'allowedFunctionNames' => [(string) $choice['name']],
                ],
            ];
        }

        return [
            'functionCallingConfig' => [
                'mode' => match ($type) {
                    'any' => 'ANY',
                    'none' => 'NONE',
                    default => 'AUTO',
                },
            ],
        ];
    }

    /**
     * Normalise Gemini's response into the shape used everywhere else.
     *
     * @param  array<string, mixed>  $data
     * @return array{content:string, raw:array<string,mixed>, stop_reason:?string, tool_calls:array<int,array<string,mixed>>}
     */
    private function normalize(array $data): array
    {
        $candidate = $data['candidates'][0] ?? [];
        $contentParts = is_array($candidate['content']['parts'] ?? null) ? $candidate['content']['parts'] : [];
        $finish = (string) ($candidate['finishReason'] ?? 'STOP');

        $text = '';
        $toolCalls = [];
        $assistantBlocks = [];

        foreach ($contentParts as $part) {
            if (! is_array($part)) {
                continue;
            }
            if (isset($part['text'])) {
                $text .= (string) $part['text'];
                $assistantBlocks[] = ['type' => 'text', 'text' => (string) $part['text']];
            }
            if (isset($part['functionCall'])) {
                $call = $part['functionCall'];
                $name = (string) ($call['name'] ?? '');
                $args = is_array($call['args'] ?? null) ? $call['args'] : [];
                // Gemini doesn't surface a per-call ID; synthesize a stable one
                // so our tool_use_id pairing for tool_result still works.
                $id = 'gem_'.substr(md5($name.json_encode($args)), 0, 16);
                $toolCalls[] = ['id' => $id, 'name' => $name, 'input' => $args];
                $assistantBlocks[] = [
                    'type' => 'tool_use',
                    'id' => $id,
                    'name' => $name,
                    'input' => $args,
                ];
            }
        }

        // Translate Gemini finish reason → Anthropic-style stop_reason.
        $stopReason = match ($finish) {
            'TOOL_CALL', 'FUNCTION_CALL' => 'tool_use',
            'MAX_TOKENS' => 'max_tokens',
            'STOP' => 'end_turn',
            default => strtolower($finish) ?: 'end_turn',
        };

        // If the model emitted a tool_use part, the canonical stop reason is
        // tool_use even if Gemini returned STOP (some Gemini versions do this).
        if ($toolCalls !== [] && $stopReason !== 'tool_use') {
            $stopReason = 'tool_use';
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
            'content' => '[MOCK GEMINI DRAFT] Set GEMINI_API_KEY in .env for real responses. '.mb_substr($lastUser, 0, 200),
            'raw' => ['mock' => true],
            'stop_reason' => 'end_turn',
            'tool_calls' => [],
        ];
    }
}
