<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasPermission
{
    /**
     * Abort with 403 unless the logged-in user may perform
     * the given action on the given module.
     * Usage: ->middleware('permission:banners,delete')
     */
    public function handle(Request $request, Closure $next, string $module, string $action): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canAccess($module, $action)) {
            abort(403);
        }

        return $next($request);
    }
}
