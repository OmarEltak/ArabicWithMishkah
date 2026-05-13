<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the /__internal/* endpoints behind a shared secret header.
 *
 * The secret is read from APP_INTERNAL_HEALTH_KEY. Monitoring services
 * (Pingdom, Better Stack, Datadog) include `X-Internal-Health-Key:
 * <secret>` on each probe.
 *
 * Constant-time comparison prevents timing attacks. If no key is
 * configured we deny by default — exposing health detail to the public
 * internet is not a "fail-open" decision.
 */
class InternalHealthGate
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('app.internal_health_key', '');
        $provided = (string) $request->header('X-Internal-Health-Key', '');

        if ($expected === '' || $provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json([
                'error' => 'Forbidden. Set X-Internal-Health-Key.',
            ], 403);
        }

        return $next($request);
    }
}
