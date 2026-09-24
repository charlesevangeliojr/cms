<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            $moduleLabel = match ($module) {
                'dashboard' => 'the dashboard',
                'banners' => 'banners',
                'users' => 'users',
                'contacts' => 'contact messages',
                'newsletters' => 'newsletter subscribers',
                default => Str::headline($module),
            };

            abort(403, "You are not allowed to {$action} {$moduleLabel}.");
        }

        return $next($request);
    }
}
