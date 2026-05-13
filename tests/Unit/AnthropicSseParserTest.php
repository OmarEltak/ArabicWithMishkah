<?php

declare(strict_types=1);

use App\Services\AI\AnthropicService;

/**
 * Exercises the SSE parser inside chatStream() without hitting the network.
 *
 * We can't call chatStream() directly without a live connection, but the
 * private handleSseEvent() method is reachable via reflection — that's
 * the most isolated unit test we can write, and it keeps the bug-prone
 * SSE state machine pinned by tests.
 */
function invokeSse(AnthropicService $svc, string $event, &$text, &$tools, &$stop, &$usage, $onDelta): void
{
    $ref = new ReflectionMethod($svc, 'handleSseEvent');
    $ref->setAccessible(true);
    $ref->invokeArgs($svc, [$event, &$text, &$tools, &$stop, &$usage, $onDelta]);
}

it('emits text deltas via the callback as they arrive', function () {
    $svc = new AnthropicService(apiKey: 'test-key');
    $captured = '';
    $onDelta = function (string $d) use (&$captured): void {
        $captured .= $d;
    };

    $text = '';
    $tools = [];
    $stop = null;
    $usage = [];

    invokeSse($svc, "event: message_start\ndata: {\"type\":\"message_start\",\"message\":{\"usage\":{\"input_tokens\":10}}}", $text, $tools, $stop, $usage, $onDelta);
    invokeSse($svc, "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":0,\"delta\":{\"type\":\"text_delta\",\"text\":\"Hello \"}}", $text, $tools, $stop, $usage, $onDelta);
    invokeSse($svc, "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":0,\"delta\":{\"type\":\"text_delta\",\"text\":\"world\"}}", $text, $tools, $stop, $usage, $onDelta);
    invokeSse($svc, "event: message_delta\ndata: {\"type\":\"message_delta\",\"delta\":{\"stop_reason\":\"end_turn\"},\"usage\":{\"output_tokens\":5}}", $text, $tools, $stop, $usage, $onDelta);

    expect($captured)->toBe('Hello world');
    expect($text)->toBe('Hello world');
    expect($stop)->toBe('end_turn');
    expect($usage['input_tokens'])->toBe(10);
    expect($usage['output_tokens'])->toBe(5);
});

it('accumulates tool_use input JSON across deltas', function () {
    $svc = new AnthropicService(apiKey: 'test-key');
    $text = '';
    $tools = [];
    $stop = null;
    $usage = [];
    $onDelta = function (): void {};

    invokeSse($svc, "event: content_block_start\ndata: {\"type\":\"content_block_start\",\"index\":1,\"content_block\":{\"type\":\"tool_use\",\"id\":\"toolu_1\",\"name\":\"lookup_law\"}}", $text, $tools, $stop, $usage, $onDelta);
    invokeSse($svc, "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":1,\"delta\":{\"type\":\"input_json_delta\",\"partial_json\":\"{\\\"query\\\":\\\"Egyptian \"}}", $text, $tools, $stop, $usage, $onDelta);
    invokeSse($svc, "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"index\":1,\"delta\":{\"type\":\"input_json_delta\",\"partial_json\":\"NDA\\\"}\"}}", $text, $tools, $stop, $usage, $onDelta);
    invokeSse($svc, "event: content_block_stop\ndata: {\"type\":\"content_block_stop\",\"index\":1}", $text, $tools, $stop, $usage, $onDelta);

    expect($tools)->toHaveCount(1);
    $tool = $tools[1];
    expect($tool['id'])->toBe('toolu_1');
    expect($tool['name'])->toBe('lookup_law');
    expect($tool['input'])->toBe(['query' => 'Egyptian NDA']);
});

it('falls through to non-streaming chat() when API key is missing', function () {
    // No API key → chat() returns mock response, chatStream() should mirror it
    // by emitting the full content as one delta and returning the same shape.
    $svc = new AnthropicService(apiKey: null);
    $captured = '';
    $response = $svc->chatStream(
        messages: [['role' => 'user', 'content' => 'hello']],
        options: [],
        onDelta: function (string $d) use (&$captured): void {
            $captured .= $d;
        },
    );

    expect($response['content'])->not->toBe('');
    expect($captured)->toBe($response['content']);
});
