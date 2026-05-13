<?php

namespace App\Providers;

use App\Services\AI\AnthropicService;
use App\Services\AI\CitationVerifier;
use App\Services\AI\EmbeddingService;
use App\Services\AI\LlmFactory;
use App\Services\AI\LlmInterface;
use App\Services\AI\RagService;
use App\Services\AI\UsageTracker;
use App\Services\Contracts\BilingualTranslator;
use App\Services\Contracts\ContractDiffer;
use App\Services\Contracts\ContractDraftingService;
use App\Services\Ingestion\EastlawsClient;
use App\Services\Ingestion\EastlawsIngestService;
use App\Services\Ingestion\FreshnessService;
use App\Services\Ingestion\IngestionService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(UsageTracker::class, fn () => UsageTracker::fromConfig());
        $this->app->singleton(AnthropicService::class, fn () => AnthropicService::fromConfig());
        $this->app->singleton(LlmInterface::class, fn () => LlmFactory::default());
        $this->app->singleton(EmbeddingService::class, fn () => EmbeddingService::fromConfig());
        $this->app->singleton(RagService::class, fn () => RagService::fromConfig());
        $this->app->singleton(CitationVerifier::class, fn () => CitationVerifier::fromConfig());
        $this->app->singleton(ContractDraftingService::class, fn () => ContractDraftingService::fromConfig());
        $this->app->singleton(BilingualTranslator::class, fn () => BilingualTranslator::fromConfig());
        $this->app->singleton(ContractDiffer::class, fn () => new ContractDiffer);
        $this->app->singleton(IngestionService::class, fn () => IngestionService::fromConfig());
        $this->app->singleton(EastlawsClient::class, fn () => EastlawsClient::fromConfig());
        $this->app->singleton(EastlawsIngestService::class, fn () => EastlawsIngestService::fromConfig());
        $this->app->singleton(FreshnessService::class, fn () => FreshnessService::fromConfig());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        // Belt-and-suspenders: in-PHP cosine over the eastlaws corpus
        // (~33K chunks) needs more than the 128M default. The .user.ini
        // file in public/ handles PHP-FPM web requests; this catches CLI
        // and any environment that didn't pick up the .ini override.
        if (function_exists('ini_get') && function_exists('ini_set')) {
            $current = self::memoryToBytes((string) ini_get('memory_limit'));
            $needed = 512 * 1024 * 1024;
            if ($current > 0 && $current < $needed) {
                ini_set('memory_limit', '512M');
            }
            // Drafting can outrun PHP's 30s default if Gemini/Anthropic are
            // slow or thinking. Mirrors public/.user.ini for CLI/tinker.
            $currentExec = (int) ini_get('max_execution_time');
            if ($currentExec > 0 && $currentExec < 180) {
                ini_set('max_execution_time', '180');
            }
        }

        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Convert a php-ini-style memory string ("128M", "1G", "-1") to bytes.
     * Returns 0 for unlimited (-1) so the bump logic skips the override.
     */
    private static function memoryToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return 0;
        }
        $unit = strtolower(substr($value, -1));
        $num = (int) $value;

        return match ($unit) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => $num,
        };
    }
}
