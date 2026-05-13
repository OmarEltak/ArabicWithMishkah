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

        // Stripe (and Tap) webhooks come from outside our app and can't
        // carry a CSRF token. Exclude their URIs from the CSRF middleware.
        $middleware->validateCsrfTokens(except: [
            'billing/webhook',
            'billing/tap-webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Send all reportable exceptions to a dedicated errors channel so
        // alerting integrations (Sentry, Slack, Better Stack) only see
        // real failures, not the routine debug chatter on the default log.
        $exceptions->report(function (Throwable $e): void {
            Log::channel('errors')->error(
                $e->getMessage(),
                [
                    'exception' => $e::class,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace_short' => collect($e->getTrace())->take(8)->all(),
                    'request_url' => request()?->fullUrl(),
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
