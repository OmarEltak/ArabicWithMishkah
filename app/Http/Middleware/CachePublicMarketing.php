<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public-page caching headers. Sets `Cache-Control: public, max-age=...`
 * with a `Vary: Cookie, Accept-Language` so CDNs / browser caches can
 * reuse responses without leaking cross-user content.
 *
 * Skips entirely for:
 *   - authenticated users (the response may contain personalised UI like
 *     the Dashboard CTA in the marketing header)
 *   - non-GET / non-HEAD requests
 *   - non-200 responses
 *
 * Apply via route middleware, not globally — only marketing routes are
 * safe to cache.
 */
class CachePublicMarketing
{
    public function handle(Request $request, Closure $next, int $maxAge = 600): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $response;
        }

        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // Authenticated users see personalised content (e.g. "Dashboard"
        // button instead of "Sign in" in the marketing header). Don't
        // cache for them — let the response stay private.
        if ($request->user() !== null) {
            $response->headers->set('Cache-Control', 'private, no-store, max-age=0');

            return $response;
        }

        // Vary on cookie and Accept-Language so:
        //  - Authenticated requests don't get served a guest-cached body
        //  - Arabic and English visitors get separate cache entries
        $response->headers->set('Cache-Control', sprintf('public, max-age=%d, s-maxage=%d', $maxAge, $maxAge));
        $response->headers->set('Vary', 'Cookie, Accept-Language', false);

        // ETag for cheap 304s on repeat hits. Compute over the body so
        // it changes when the rendered HTML changes (e.g., updated corpus
        // counts on the welcome page).
        $content = $response->getContent();
        if ($content !== false && $content !== '') {
            $etag = '"'.md5($content).'"';
            $response->headers->set('ETag', $etag);

            $ifNoneMatch = $request->headers->get('If-None-Match');
            if ($ifNoneMatch === $etag) {
                $response->setStatusCode(304);
                $response->setContent('');
            }
        }

        return $response;
    }
}
