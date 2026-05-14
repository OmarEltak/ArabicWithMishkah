<?php

use App\Http\Middleware\CachePublicMarketing;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocaleFromUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Run on every web request so the locale is set BEFORE controllers
        // and Blade views resolve translation keys.
        $middleware->web(append: [
            SetLocaleFromUser::class,
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'cache.public' => CachePublicMarketing::class,
        ]);

        // Stripe webhook comes from outside our app and can't carry a
        // CSRF token. Re-add other webhook URIs ONLY when the route is
        // actually registered AND its signature-verification middleware
        // is wired — otherwise the exclusion sits as a latent
        // CSRF-free POST surface waiting for a future endpoint to attach.
        $middleware->validateCsrfTokens(except: [
            'billing/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Send all reportable exceptions to a dedicated errors channel so
        // alerting integrations (Sentry, Slack, Better Stack) only see
        // real failures, not the routine debug chatter on the default log.
        $exceptions->report(function (Throwable $e): void {
            // Strip the deployment directory prefix so Slack/Sentry alerts
            // don't expose the absolute filesystem path. Use ->url() (path
            // only) not ->fullUrl() so a search query / contract id in a
            // GET querystring doesn't propagate into the error sink.
            $basePath = base_path().DIRECTORY_SEPARATOR;
            $file = str_replace($basePath, '', (string) $e->getFile());

            Log::channel('errors')->error(
                $e->getMessage(),
                [
                    'exception' => $e::class,
                    'file' => $file,
                    'line' => $e->getLine(),
                    'trace_short' => collect($e->getTrace())->take(8)->all(),
                    'request_url' => request()?->url(),
                    'user_id' => optional(auth()->user())->id,
                ],
            );
        });

        // Don't double-report the same exception via the default handler.
        $exceptions->dontReport([
            // (Add custom-handled domain exceptions here as the codebase
            // grows. Right now there are none.)
        ]);
    })->create();
