<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    /**
     * Log out accounts idle longer than the configured timeout.
     *
     * Any authenticated activity refreshes the timestamp, so only true
     * idleness expires the session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() === null) {
            return $next($request);
        }

        $timeout = max((int) config('session.timeout', 30), 1) * 60;
        $lastSeen = (int) $request->session()->get('last_seen', 0);

        if ($lastSeen > 0 && now()->getTimestamp() - $lastSeen > $timeout) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your session expired due to inactivity. Please sign in again.',
            ]);
        }

        // Automated dashboard polling must not keep an idle admin signed in.
        if (! $request->routeIs('dashboard.traffic') || $lastSeen === 0) {
            $request->session()->put('last_seen', now()->getTimestamp());
        }

        return $next($request);
    }
}
