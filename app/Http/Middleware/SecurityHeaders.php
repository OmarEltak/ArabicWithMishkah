<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline browser-side security headers for every web response. Tightens
 * defaults that browsers leave permissive, without breaking the fonts.bunny.net
 * + fonts.googleapis.com font CDNs the marketing pages depend on.
 *
 * HSTS is sent only when the request is over HTTPS — otherwise we'd lock
 * local HTTP development out of the site.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $headers = $response->headers;

        // Don't sniff content types away from the declared Content-Type.
        $headers->set('X-Content-Type-Options', 'nosniff', false);

        // Disallow framing by other origins (clickjacking).
        $headers->set('X-Frame-Options', 'SAMEORIGIN', false);

        // Send Referer only to same-origin and never expose path/query
        // cross-origin (legal-AI search queries can contain PII).
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);

        // Disable the legacy Permissions-Policy features we don't use.
        $headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
            false,
        );

        // Strict-Transport-Security only over HTTPS, and only outside the
        // local dev environment so `herd` HTTP doesn't get pinned.
        if ($request->isSecure() && ! app()->environment('local')) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains', false);
        }

        // Content-Security-Policy — second line of defence against any
        // future stored-XSS exploit. 'unsafe-inline' on script is required
        // by Livewire's inline event handlers and Alpine.js x-data; tighten
        // to nonces in a follow-up after the inline-handler audit. Style
        // 'unsafe-inline' is required for Flux's component-scoped styles
        // and the dark-mode swap. Font hosts are the two we use:
        // fonts.bunny.net (privacy-respecting) and fonts.googleapis.com.
        // frame-ancestors 'none' replaces X-Frame-Options for CSP-aware browsers.
        $headers->set(
            'Content-Security-Policy',
            "default-src 'self'; "
            ."script-src 'self' 'unsafe-inline' 'unsafe-eval'; "
            ."style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com; "
            ."font-src 'self' data: https://fonts.bunny.net https://fonts.gstatic.com; "
            ."img-src 'self' data: blob:; "
            ."connect-src 'self'; "
            ."frame-ancestors 'none'; "
            ."base-uri 'self'; "
            ."form-action 'self' https://checkout.stripe.com https://billing.stripe.com;",
            false,
        );

        // Health and webhook endpoints are not HTML — they should never be
        // indexed, even if a proxy caches them.
        if ($request->is('__internal/*') || $request->is('billing/webhook')) {
            $headers->set('X-Robots-Tag', 'noindex, nofollow', false);
        }

        return $response;
    }
}
