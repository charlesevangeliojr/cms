<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCmsModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        abort_unless(array_key_exists($module, config('cms.pages', [])), 404);

        return $next($request);
    }
}
