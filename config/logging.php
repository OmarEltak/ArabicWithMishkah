<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', (string) env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        // ─────────────────────────────────────────────────────────────────
        // Errors-only channel.
        // Isolates ERROR / CRITICAL / ALERT / EMERGENCY records from the
        // chatty default log so an alerting integration (Sentry, Slack
        // webhook, Better Stack) only fires on real problems.
        //
        // This is a `stack` so multiple sinks can attach. The default is
        // file-only; set LOG_ERRORS_STACK=errors_file,slack or
        // LOG_ERRORS_STACK=errors_file,sentry once those channels are
        // configured to fan out alerts.
        // ─────────────────────────────────────────────────────────────────
        'errors' => [
            'driver' => 'stack',
            'channels' => explode(',', (string) env('LOG_ERRORS_STACK', 'errors_file')),
            'ignore_exceptions' => false,
        ],

        'errors_file' => [
            'driver' => 'daily',
            'path' => storage_path('logs/errors.log'),
            'level' => 'error',
            'days' => env('LOG_ERRORS_DAYS', 30),
            'replace_placeholders' => true,
        ],

        // Sentry channel — registered as a config block so it shows up in
        // LOG_ERRORS_STACK even before sentry/sentry-laravel is installed.
        // Laravel will only resolve it when actually used, so it's a no-op
        // until you run `composer require sentry/sentry-laravel` and set
        // SENTRY_LARAVEL_DSN.
        'sentry' => [
            'driver' => 'sentry',
            'level' => env('SENTRY_LOG_LEVEL', 'error'),
            'bubble' => true,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', env('APP_NAME', 'Laravel')),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        // Dedicated channel for AI/external-API observability events. Anything
        // that touches Anthropic, Voyage, OpenAI, or eastlaws should log here
        // with structured context — that way ops can tail one file to spot
        // latency regressions, rate-limit hits, or authentication issues
        // without sifting through framework noise.
        'ai' => [
            'driver' => 'daily',
            'path' => storage_path('logs/ai.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'days' => 30,
            'replace_placeholders' => true,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

    ],

];
