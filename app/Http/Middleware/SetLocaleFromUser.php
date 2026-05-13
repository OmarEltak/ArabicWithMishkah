<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Sets the active locale from the authenticated user's `locale` column,
 * falling back to a session value (for guests who toggled the language on
 * the login screen) and finally to the app default. Runs early in the web
 * stack so every translation lookup, every Blade view, and every redirect
 * sees the right locale.
 */
class SetLocaleFromUser
{
    public function handle(Request $request, Closure $next)
    {
        $supported = ['en', 'ar'];

        $locale = $request->user()?->locale
            ?? $request->session()->get('locale')
            ?? config('app.locale', 'en');

        if (! in_array($locale, $supported, true)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
