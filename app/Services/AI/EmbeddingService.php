<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class EmbeddingService
{
    public function __construct(
        private readonly string $provider = 'voyage',
        private readonly ?string $voyageKey = null,
        private readonly string $voyageModel = 'voyage-3',
        private readonly string $voyageBaseUrl = 'https://api.voyageai.com/v1',
        private readonly ?string $openaiKey = null,
        private readonly string $openaiModel = 'text-embedding-3-small',
        private readonly string $openaiBaseUrl = 'https://api.openai.com/v1',
        private readonly ?UsageTracker $tracker = null,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            provider: (string) config('lawyer.embedding_provider', 'voyage'),
            voyageKey: (string) (config('services.voyage.api_key') ?? ''),
            voyageModel: (string) config('services.voyage.model', 'voyage-3'),
            voyageBaseUrl: rtrim((string) config('services.voyage.base_url', 'https://api.voyageai.com/v1'), '/'),
            openaiKey: (string) (config('services.openai.api_key') ?? ''),
            openaiModel: (string) config('services.openai.embedding_model', 'text-embedding-3-small'),
            openaiBaseUrl: rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/'),
            tracker: app(UsageTracker::class),
        );
    }

    public function isConfigured(): bool
    {
        return match ($this->provider) {
            'voyage' => $this->voyageKey !== '',
            'openai' => $this->openaiKey !== '',
            default => false,
        };
    }

    public function modelName(): string
    {
        return match ($this->provider) {
            'voyage' => 'voyage:'.$this->voyageModel,
            'openai' => 'openai:'.$this->openaiModel,
            default => 'mock:hash',
        };
    }

    /**
     * Embed a list of strings.
     *
     * @param  array<int, string>  $inputs
     * @return array<int, array<int, float>>
     */
    public function embedBatch(array $inputs): array
    {
        if (count($inputs) === 0) {
            return [];
        }

        if (! $this->isConfigured()) {
            return array_map(fn (string $t) => $this->mockEmbed($t), $inputs);
        }

        return match ($this->provider) {
            'voyage' => $this->embedVoyage($inputs),
            'openai' => $this->embedOpenAi($inputs),
            default => array_map(fn (string $t) => $this->mockEmbed($t), $inputs),
        };
    }

    /** @return array<int, float> */
    public function embedOne(string $input): array
    {
        $batch = $this->embedBatch([$input]);

        return $batch[0] ?? [];
    }

    /**
     * @param  array<int, string>  $inputs
     * @return array<int, array<int, float>>
     */
    private function embedVoyage(array $inputs): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->voyageKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout(60)
            ->post($this->voyageBaseUrl.'/embeddings', [
                'model' => $this->voyageModel,
                'input' => $inputs,
                'input_type' => 'document',
            ]);

        if ($response->failed()) {
            Log::channel('ai')->warning('Voyage embedding failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Embedding request failed: '.$response->status());
        }

        $data = $response->json();
        $vectors = [];
        foreach (($data['data'] ?? []) as $row) {
            $vectors[] = array_values(array_map('floatval', (array) ($row['embedding'] ?? [])));
        }

        $this->tracker?->record(
            provider: 'voyage',
            operation: 'embed',
            model: $this->voyageModel,
            inputTokens: (int) ($data['usage']['total_tokens'] ?? 0),
            outputTokens: 0,
            metadata: ['batch_size' => count($inputs)],
        );

        return $vectors;
    }

    /**
     * @param  array<int, string>  $inputs
     * @return array<int, array<int, float>>
     */
    private function embedOpenAi(array $inputs): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->openaiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout(60)
            ->post($this->openaiBaseUrl.'/embeddings', [
                'model' => $this->openaiModel,
                'input' => $inputs,
            ]);

        if ($response->failed()) {
            Log::channel('ai')->warning('OpenAI embedding failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Embedding request failed: '.$response->status());
        }

        $data = $response->json();
        $vectors = [];
        foreach (($data['data'] ?? []) as $row) {
            $vectors[] = array_values(array_map('floatval', (array) ($row['embedding'] ?? [])));
        }

        $this->tracker?->record(
            provider: 'openai',
            operation: 'embed',
            model: $this->openaiModel,
            inputTokens: (int) ($data['usage']['prompt_tokens'] ?? $data['usage']['total_tokens'] ?? 0),
            outputTokens: 0,
            metadata: ['batch_size' => count($inputs)],
        );

        return $vectors;
    }

    /**
     * Deterministic mock embedding (256d) so RAG remains usable in tests/dev without keys.
     * Uses repeated SHA-256 hashing of the input — NOT semantic, but stable.
     *
     * @return array<int, float>
     */
    private function mockEmbed(string $text): array
    {
        $dim = 256;
        $vector = [];
        $seed = $text;
        while (count($vector) < $dim) {
            $hash = hash('sha256', $seed, true);
            for ($i = 0; $i < strlen($hash) && count($vector) < $dim; $i++) {
                $vector[] = (ord($hash[$i]) - 127.5) / 127.5;
            }
            $seed = $hash;
        }

        return self::normalize($vector);
    }

    /**
     * @param  array<int, float>  $v
     * @return array<int, float>
     */
    public static function normalize(array $v): array
    {
        $sum = 0.0;
        foreach ($v as $x) {
            $sum += $x * $x;
        }
        $norm = sqrt($sum);
        if ($norm <= 0.0) {
            return $v;
        }

        return array_map(fn (float $x) => $x / $norm, $v);
    }
}
