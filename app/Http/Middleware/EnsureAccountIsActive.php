<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Log out accounts deactivated after they signed in.
     *
     * The authenticated user is refreshed from the database so toggling
     * Active Account takes effect on the next request. The refreshed user
     * is also supplied to later middleware and controllers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authenticatedId = $request->user()?->getAuthIdentifier();

        if ($authenticatedId === null) {
            return $next($request);
        }

        $user = User::find($authenticatedId);

        if (! $user || ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'This account has been deactivated. Contact an administrator.',
            ]);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
