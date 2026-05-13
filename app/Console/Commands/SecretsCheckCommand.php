<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Pre-deploy / pre-launch sanity check on environment configuration.
 *
 *   php artisan secrets:check          # human-readable table
 *   php artisan secrets:check --json   # machine-readable for CI
 *   php artisan secrets:check --strict # exits 1 if ANY recommended is missing
 *
 * Inspects every required env var by category (app, audit, mail, billing,
 * LLM, embedding, observability) and reports presence + a one-line hint
 * when something is missing. Exits non-zero when a production-critical
 * secret is missing in a non-local environment so the deploy pipeline
 * fails loudly rather than serving a broken site.
 *
 * Why an artisan command and not a startup check:
 *   - Startup checks slow every request and risk locking out a recovering
 *     site (chicken-and-egg if the secret manager itself is the problem)
 *   - A deploy-time `secrets:check` runs once, after env vars are loaded,
 *     before traffic flips. Both pipelines and humans can run it.
 */
class SecretsCheckCommand extends Command
{
    protected $signature = 'secrets:check
                            {--json : Emit a JSON report instead of a human table}
                            {--strict : Exit 1 on any recommended-missing, not just required-missing}';

    protected $description = 'Audit every required environment secret and report what is set vs missing';

    public function handle(): int
    {
        $isProd = app()->environment('production');
        $isJson = (bool) $this->option('json');
        $strict = (bool) $this->option('strict');

        $checks = $this->checks($isProd);

        $rows = [];
        $missingRequired = 0;
        $missingRecommended = 0;

        foreach ($checks as $category => $items) {
            foreach ($items as $item) {
                $present = $this->isPresent($item);
                $rows[] = [
                    'category' => $category,
                    'key' => $item['key'],
                    'required' => $item['required'],
                    'present' => $present,
                    'hint' => $present ? null : $item['hint'],
                ];

                if (! $present) {
                    if ($item['required']) {
                        $missingRequired++;
                    } else {
                        $missingRecommended++;
                    }
                }
            }
        }

        if ($isJson) {
            $this->line(json_encode([
                'environment' => app()->environment(),
                'missing_required' => $missingRequired,
                'missing_recommended' => $missingRecommended,
                'checks' => $rows,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->renderTable($rows);
            $this->newLine();
            $this->line(sprintf('Environment: <fg=cyan>%s</>', app()->environment()));
            $this->line(sprintf(
                '<fg=red>%d</> required missing · <fg=yellow>%d</> recommended missing',
                $missingRequired,
                $missingRecommended,
            ));
        }

        // Exit codes:
        //   0  — all required present (recommended optional)
        //   1  — at least one required missing, OR --strict and recommended missing
        //   In non-production we never fail on required either; this is a
        //   local-dev tool too.
        if (! $isProd) {
            return self::SUCCESS;
        }

        if ($missingRequired > 0) {
            return self::FAILURE;
        }

        if ($strict && $missingRecommended > 0) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, array<int, array{key:string, required:bool, hint:string}>>
     */
    private function checks(bool $isProd): array
    {
        return [
            'app' => [
                $this->required('APP_KEY', 'Run `php artisan key:generate`. Leaving this empty breaks every encrypted cookie, queued job, and password reset.'),
                $this->required('APP_URL', 'Set to your canonical https://… URL. Used in route(), mail links, OG tags.'),
                $this->required('APP_AUDIT_KEY', 'Generate with `php -r "echo bin2hex(random_bytes(32)).PHP_EOL;"`. Production refuses to fall back to APP_KEY.'),
                $this->recommended('APP_INTERNAL_HEALTH_KEY', 'Sets the X-Internal-Health-Key required to poll /__internal/health. Leave empty to disable that endpoint entirely.'),
            ],
            'database' => [
                $this->required('DB_CONNECTION', 'sqlite | pgsql | mysql. Pick before migrating.'),
            ],
            'mail' => [
                $this->required('MAIL_MAILER', 'smtp | postmark | resend | ses. Must not be "log" in production — verification emails go to /dev/null.'),
                $this->required('MAIL_FROM_ADDRESS', 'no-reply@yourdomain. Fortify uses this for password-reset + verification mail.'),
                $this->required('MAIL_FROM_NAME', 'Human-readable sender name shown in mail clients.'),
            ],
            'observability' => [
                $this->recommended('LOG_ERRORS_STACK', 'Comma-separated list (errors_file,slack | errors_file,sentry). Default errors_file writes to disk only — production errors will be invisible to your team.'),
                $this->recommended('LOG_SLACK_WEBHOOK_URL', 'Slack incoming webhook for error alerting. Only used when LOG_ERRORS_STACK includes "slack".'),
                $this->recommended('SENTRY_LARAVEL_DSN', 'Sentry DSN. Only used when sentry/sentry-laravel is installed and LOG_ERRORS_STACK includes "sentry".'),
            ],
            'billing' => [
                $this->recommended('STRIPE_KEY', 'Publishable key (pk_live_… or pk_test_…). Required for any paid plan.'),
                $this->recommended('STRIPE_SECRET', 'Secret key (sk_live_… or sk_test_…).'),
                $this->recommended('STRIPE_WEBHOOK_SECRET', 'Signing secret from Stripe Dashboard → Webhooks. Required for webhook signature verification.'),
                $this->recommended('STRIPE_PRICE_SOLO_MONTHLY', 'price_… from Stripe Dashboard. Without this the Solo plan checkout 503s.'),
                $this->recommended('STRIPE_PRICE_SOLO_YEARLY', 'price_… from Stripe Dashboard.'),
                $this->recommended('STRIPE_PRICE_FIRM_MONTHLY', 'price_… from Stripe Dashboard.'),
                $this->recommended('STRIPE_PRICE_FIRM_YEARLY', 'price_… from Stripe Dashboard.'),
            ],
            'llm' => [
                $this->recommended('ANTHROPIC_API_KEY', 'sk-ant-… — primary LLM provider. Without it, drafting falls through to Gemini/Groq if those are configured.'),
                $this->recommended('GEMINI_API_KEY', 'Google AI Studio key. Used for clarifying-Q rounds and as a fallback LLM.'),
                $this->recommended('GROQ_API_KEY', 'Groq key. Cheapest fallback for high-volume / low-stakes calls.'),
                $this->recommended('VOYAGE_API_KEY', 'Embeddings provider. Required for RAG retrieval; without it search falls back to keyword-only.'),
            ],
            'corpus' => [
                $this->recommended('EASTLAWS_ENABLED', 'true to enable ingestion against the upstream legal database. Requires EASTLAWS_USERNAME + EASTLAWS_PASSWORD if true.'),
            ],
            'security' => [
                $this->recommended($isProd ? 'SESSION_SECURE_COOKIE' : '__skip', 'Set true in production for HTTPS-only cookies. Defaults to true outside local already.'),
            ],
        ];
    }

    /**
     * @return array{key:string, required:bool, hint:string}
     */
    private function required(string $key, string $hint): array
    {
        return ['key' => $key, 'required' => true, 'hint' => $hint];
    }

    /**
     * @return array{key:string, required:bool, hint:string}
     */
    private function recommended(string $key, string $hint): array
    {
        return ['key' => $key, 'required' => false, 'hint' => $hint];
    }

    /**
     * @param  array{key:string, required:bool, hint:string}  $item
     */
    private function isPresent(array $item): bool
    {
        // Allow rows tagged with "__skip" to be no-ops (e.g. env-conditional checks).
        if ($item['key'] === '__skip') {
            return true;
        }
        $value = env($item['key']);

        return $value !== null && $value !== '' && $value !== 'null';
    }

    /**
     * @param  array<int, array{category:string, key:string, required:bool, present:bool, hint:string|null}>  $rows
     */
    private function renderTable(array $rows): void
    {
        $tableRows = [];
        foreach ($rows as $r) {
            if ($r['key'] === '__skip') {
                continue;
            }
            $status = $r['present']
                ? '<fg=green>✓</>'
                : ($r['required'] ? '<fg=red>✗ required</>' : '<fg=yellow>○ recommended</>');
            $hint = $r['present'] ? '' : (string) ($r['hint'] ?? '');
            $tableRows[] = [$r['category'], $r['key'], $status, str(strip_tags($hint))->limit(72)];
        }
        $this->table(['Category', 'Env var', 'Status', 'Hint (if missing)'], $tableRows);
    }
}
