<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Vendor-agnostic chat completion interface. Implementations adapt their
 * native wire format (Anthropic's tool_use blocks, OpenAI's tool_calls, etc.)
 * to a single normalized response shape so callers don't have to care which
 * provider is configured.
 *
 * Tool definitions are passed in **Anthropic format** because the project
 * was built around Claude first; OpenAI-compatible adapters translate to
 * `function`/`parameters` shape on the way out.
 */
interface LlmInterface
{
    /**
     * @param  array<int, array{role:string, content:string|array}>  $messages
     * @param  array<string, mixed>  $options  system, max_tokens, temperature, tools, tool_choice
     * @return array{content:string, raw:array<string,mixed>, stop_reason:?string, tool_calls:array<int,array<string,mixed>>}
     */
    public function chat(array $messages, array $options = []): array;

    /**
     * Streaming variant. Calls $onDelta($text) for each text delta. Returns
     * the same shape as chat() once the stream completes.
     *
     * @param  array<int, array{role:string, content:string|array}>  $messages
     * @param  array<string, mixed>  $options
     * @return array{content:string, raw:array<string,mixed>, stop_reason:?string, tool_calls:array<int,array<string,mixed>>}
     */
    public function chatStream(array $messages, array $options, callable $onDelta): array;

    public function isConfigured(): bool;
}
