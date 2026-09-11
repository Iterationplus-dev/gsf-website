<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Preserves inbound links to the previous website.
 *
 * The map is cached, so the common case - no redirect for this path - costs one
 * array lookup. Administrators add entries in the CMS whenever a slug changes,
 * which is what stops the site losing search rankings each time content moves.
 */
class HandleLegacyRedirects
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = '/'.trim($request->path(), '/');

        $target = Redirect::map()[$path] ?? null;

        if ($target !== null && $target !== $path) {
            return redirect($target, 301);
        }

        return $next($request);
    }
}
