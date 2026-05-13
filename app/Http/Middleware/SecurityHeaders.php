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

        // Health and webhook endpoints are not HTML — they should never be
        // indexed, even if a proxy caches them.
        if ($request->is('__internal/*') || $request->is('billing/webhook')) {
            $headers->set('X-Robots-Tag', 'noindex, nofollow', false);
        }

        return $response;
    }
}
