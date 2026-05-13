<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AnthropicService implements LlmInterface
{
    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly string $model = 'claude-sonnet-4-6',
        private readonly string $baseUrl = 'https://api.anthropic.com',
        private readonly string $version = '2023-06-01',
        private readonly ?UsageTracker $tracker = null,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            apiKey: (string) (config('services.anthropic.api_key') ?? ''),
            model: (string) config('services.anthropic.model', 'claude-sonnet-4-6'),
            baseUrl: rtrim((string) config('services.anthropic.base_url', 'https://api.anthropic.com'), '/'),
            version: (string) config('services.anthropic.version', '2023-06-01'),
            tracker: app(UsageTracker::class),
        );
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== null && $this->apiKey !== '';
    }

    /**
     * Send a chat completion request.
     *
     * @param  array<int, array{role:string, content:string|array}>  $messages
     * @param  array<string, mixed>  $options  system, max_tokens, temperature, tools, tool_choice
     * @return array{content:string, raw:array<string,mixed>, stop_reason:?string, tool_calls:array<int,array<string,mixed>>}
     */
    public function chat(array $messages, array $options = []): array
    {
        if (! $this->isConfigured()) {
            return $this->mockResponse($messages, $options);
        }

        // Budget enforcement happens BEFORE the API call. If the user has hit
        // their cap, we'd rather refuse than incur cost we'll have to refund.
        $this->tracker?->assertWithinBudget();

        $payload = [
            'model' => $this->model,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'messages' => $messages,
        ];

        if (! empty($options['system'])) {
            $payload['system'] = $options['system'];
        }
        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }
        if (! empty($options['tools'])) {
            $payload['tools'] = $options['tools'];
        }
        if (! empty($options['tool_choice'])) {
            $payload['tool_choice'] = $options['tool_choice'];
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->version,
                'content-type' => 'application/json',
            ])
                ->timeout(120)
                ->post($this->baseUrl.'/v1/messages', $payload);
        } catch (ConnectionException $e) {
            Log::channel('ai')->warning('Anthropic connection failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('AI service is unreachable. Please try again.', 0, $e);
        }

        if ($response->failed()) {
            Log::channel('ai')->warning('Anthropic API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            $this->tracker?->record(
                provider: 'anthropic',
                operation: 'chat',
                model: $this->model,
                inputTokens: 0,
                outputTokens: 0,
                status: 'error',
                errorMessage: 'HTTP '.$response->status(),
            );
            throw new RuntimeException('AI request failed: '.$response->status());
        }

        $data = $response->json();
        $usage = is_array($data['usage'] ?? null) ? $data['usage'] : [];
        $this->tracker?->record(
            provider: 'anthropic',
            operation: 'chat',
            model: $this->model,
            inputTokens: (int) ($usage['input_tokens'] ?? 0),
            outputTokens: (int) ($usage['output_tokens'] ?? 0),
            cacheReadTokens: (int) ($usage['cache_read_input_tokens'] ?? 0),
            cacheCreationTokens: (int) ($usage['cache_creation_input_tokens'] ?? 0),
            metadata: ['stop_reason' => $data['stop_reason'] ?? null],
        );

        return $this->normalizeResponse(is_array($data) ? $data : []);
    }

    /**
     * Streaming variant of chat(). Calls $onDelta($text) for every text delta
     * pushed by the SSE stream, and returns the same final shape as chat()
     * once the message completes. Tool-use deltas are accumulated silently
     * (callers can inspect tool_calls in the return value) since there's no
     * meaningful progressive UI for tool input JSON.
     *
     * Falls through to the non-streaming chat() when:
     *  - the API key is missing (mock mode), OR
     *  - the caller passes tools (Anthropic streams tools but the in-process
     *    tool-use loop in ContractDraftingService is simpler against the
     *    non-stream API; ship streaming for the no-tools path first).
     *
     * @param  array<int, array{role:string, content:string|array}>  $messages
     * @param  array<string, mixed>  $options
     * @return array{content:string, raw:array<string,mixed>, stop_reason:?string, tool_calls:array<int,array<string,mixed>>}
     */
    public function chatStream(array $messages, array $options, callable $onDelta): array
    {
        if (! $this->isConfigured() || ! empty($options['tools'])) {
            // Mock or tool-use path: emit the full response as a single delta
            // so the caller's UI behaves the same.
            $response = $this->chat($messages, $options);
            if ($response['content'] !== '') {
                $onDelta($response['content']);
            }

            return $response;
        }

        $this->tracker?->assertWithinBudget();

        $payload = [
            'model' => $this->model,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'messages' => $messages,
            'stream' => true,
        ];
        if (! empty($options['system'])) {
            $payload['system'] = $options['system'];
        }
        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }

        $accumulatedText = '';
        $toolCalls = [];
        $stopReason = null;
        $usage = ['input_tokens' => 0, 'output_tokens' => 0, 'cache_read_input_tokens' => 0, 'cache_creation_input_tokens' => 0];

        $buffer = '';
        $writeCallback = function ($ch, string $chunk) use (
            &$buffer, &$accumulatedText, &$toolCalls, &$stopReason, &$usage, $onDelta
        ): int {
            $buffer .= $chunk;
            // SSE messages are separated by blank lines.
            while (($pos = strpos($buffer, "\n\n")) !== false) {
                $event = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 2);
                $this->handleSseEvent($event, $accumulatedText, $toolCalls, $stopReason, $usage, $onDelta);
            }

            return strlen($chunk);
        };

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl.'/v1/messages',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'x-api-key: '.$this->apiKey,
                'anthropic-version: '.$this->version,
                'content-type: application/json',
                'accept: text/event-stream',
            ],
            CURLOPT_WRITEFUNCTION => $writeCallback,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => false, // streaming via WRITEFUNCTION
        ]);

        $ok = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0 || $status >= 400) {
            Log::channel('ai')->warning('Anthropic SSE stream failed', [
                'status' => $status,
                'errno' => $errno,
                'error' => $error,
            ]);
            $this->tracker?->record(
                provider: 'anthropic',
                operation: 'chat.stream',
                model: $this->model,
                inputTokens: 0,
                outputTokens: 0,
                status: 'error',
                errorMessage: 'HTTP '.$status.' '.$error,
            );
            throw new RuntimeException('AI streaming request failed: '.$status);
        }

        $this->tracker?->record(
            provider: 'anthropic',
            operation: 'chat.stream',
            model: $this->model,
            inputTokens: $usage['input_tokens'] ?? 0,
            outputTokens: $usage['output_tokens'] ?? 0,
            cacheReadTokens: $usage['cache_read_input_tokens'] ?? 0,
            cacheCreationTokens: $usage['cache_creation_input_tokens'] ?? 0,
            metadata: ['stop_reason' => $stopReason],
        );

        return [
            'content' => trim($accumulatedText),
            'raw' => ['stream' => true, 'usage' => $usage, 'stop_reason' => $stopReason],
            'stop_reason' => $stopReason,
            'tool_calls' => array_values($toolCalls),
        ];
    }

    /**
     * Parse one SSE event ("event: x\ndata: {...}") and apply its delta.
     *
     * @param  array<string, int>  $usage  in/out: token usage accumulator
     * @param  array<int, array<string,mixed>>  $toolCalls  in/out
     */
    private function handleSseEvent(
        string $event,
        string &$accumulatedText,
        array &$toolCalls,
        ?string &$stopReason,
        array &$usage,
        callable $onDelta,
    ): void {
        $dataLines = [];
        foreach (explode("\n", $event) as $line) {
            if (str_starts_with($line, 'data: ')) {
                $dataLines[] = substr($line, 6);
            }
        }
        if ($dataLines === []) {
            return;
        }
        $data = json_decode(implode("\n", $dataLines), true);
        if (! is_array($data)) {
            return;
        }

        $type = (string) ($data['type'] ?? '');
        switch ($type) {
            case 'content_block_start':
                $block = $data['content_block'] ?? [];
                if (($block['type'] ?? null) === 'tool_use') {
                    $idx = (int) ($data['index'] ?? count($toolCalls));
                    $toolCalls[$idx] = [
                        'id' => (string) ($block['id'] ?? ''),
                        'name' => (string) ($block['name'] ?? ''),
                        'input_json' => '',
                        'input' => [],
                    ];
                }
                break;
            case 'content_block_delta':
                $delta = $data['delta'] ?? [];
                $deltaType = (string) ($delta['type'] ?? '');
                if ($deltaType === 'text_delta') {
                    $text = (string) ($delta['text'] ?? '');
                    if ($text !== '') {
                        $accumulatedText .= $text;
                        $onDelta($text);
                    }
                } elseif ($deltaType === 'input_json_delta') {
                    $idx = (int) ($data['index'] ?? 0);
                    if (isset($toolCalls[$idx])) {
                        $toolCalls[$idx]['input_json'] .= (string) ($delta['partial_json'] ?? '');
                    }
                }
                break;
            case 'content_block_stop':
                $idx = (int) ($data['index'] ?? -1);
                if ($idx >= 0 && isset($toolCalls[$idx]) && $toolCalls[$idx]['input_json'] !== '') {
                    $decoded = json_decode($toolCalls[$idx]['input_json'], true);
                    $toolCalls[$idx]['input'] = is_array($decoded) ? $decoded : [];
                }
                break;
            case 'message_delta':
                $stopReason = (string) ($data['delta']['stop_reason'] ?? $stopReason);
                $u = $data['usage'] ?? [];
                if (isset($u['output_tokens'])) {
                    $usage['output_tokens'] = (int) $u['output_tokens'];
                }
                break;
            case 'message_start':
                $u = $data['message']['usage'] ?? [];
                $usage['input_tokens'] = (int) ($u['input_tokens'] ?? 0);
                $usage['cache_read_input_tokens'] = (int) ($u['cache_read_input_tokens'] ?? 0);
                $usage['cache_creation_input_tokens'] = (int) ($u['cache_creation_input_tokens'] ?? 0);
                break;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{content:string, raw:array<string,mixed>, stop_reason:?string, tool_calls:array<int,array<string,mixed>>}
     */
    private function normalizeResponse(array $data): array
    {
        $text = '';
        $toolCalls = [];
        foreach (($data['content'] ?? []) as $block) {
            if (! is_array($block)) {
                continue;
            }
            if (($block['type'] ?? null) === 'text') {
                $text .= (string) ($block['text'] ?? '');
            } elseif (($block['type'] ?? null) === 'tool_use') {
                $toolCalls[] = [
                    'id' => (string) ($block['id'] ?? ''),
                    'name' => (string) ($block['name'] ?? ''),
                    'input' => $block['input'] ?? [],
                ];
            }
        }

        return [
            'content' => trim($text),
            'raw' => $data,
            'stop_reason' => $data['stop_reason'] ?? null,
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
        // Deterministic mock used when ANTHROPIC_API_KEY is missing — keeps dev/tests usable.
        $lastUser = '';
        foreach (array_reverse($messages) as $msg) {
            if (($msg['role'] ?? null) === 'user') {
                $lastUser = is_string($msg['content']) ? $msg['content'] : json_encode($msg['content']);
                break;
            }
        }
        $system = (string) ($options['system'] ?? '');

        $signature = strtolower($system.' '.$lastUser);
        if (str_contains($signature, 'json') && str_contains($signature, 'questions')) {
            return [
                'content' => json_encode([
                    'questions' => [
                        'What are the full legal names of the parties?',
                        'What is the effective date of the agreement?',
                        'What jurisdiction governs the agreement?',
                    ],
                    'ready_to_draft' => false,
                ]),
                'raw' => ['mock' => true],
                'stop_reason' => 'end_turn',
                'tool_calls' => [],
            ];
        }

        return [
            'content' => "[MOCK DRAFT]\n\nThis is a mocked AI response because ANTHROPIC_API_KEY is not configured. "
                ."Set the key in your .env file to enable real drafting.\n\nLast user input: ".mb_substr($lastUser, 0, 400),
            'raw' => ['mock' => true],
            'stop_reason' => 'end_turn',
            'tool_calls' => [],
        ];
    }
}
