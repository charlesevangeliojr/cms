<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    private const LOCKOUT_BASE_SECONDS = 10;
    private const LOCKOUT_MAX_SECONDS = 900;
    private const MAX_SESSIONS_PER_USER = 3;
    public function showLogin()
    {
        if (Auth::check()) {
            $landingRouteName = Auth::user()->landingRouteName();

            if (! $landingRouteName) {
                Auth::logout();

                return redirect()->route('login')->withErrors([
                    'email' => 'This account does not have access to any admin module.',
                ]);
            }

            return redirect()->route($landingRouteName);
        }

        return view('backend.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');
        $email = strtolower($credentials['email']);

        $remaining = $this->lockoutRemaining($request, $email);
        if ($remaining > 0) {
            $strikes = $this->lockoutStrikes($request, $email);

            return back()
                ->withErrors(['email' => $this->lockoutMessage($strikes, $remaining)])
                ->onlyInput('email')
                ->with('lockout_seconds', $remaining);
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            if (! Auth::user()->is_active && ! Auth::user()->is_protected) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This account has been deactivated. Contact an administrator.',
                ])->onlyInput('email');
            }

            $user = Auth::user();
            $landingRouteName = $user->landingRouteName();

            if (! $landingRouteName) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'This account does not have access to any admin module.',
                ])->onlyInput('email');
            }

            Cache::forget($this->lockoutKey($request, $email));
            $this->enforceSessionLimit($user->getAuthIdentifier());

            return redirect()->intended(route($landingRouteName));
        }

        $seconds = $this->recordFailedAttempt($request, $email);

        return back()
            ->withErrors(['email' => $this->lockoutMessage($this->lockoutStrikes($request, $email), $seconds)])
            ->onlyInput('email')
            ->with('lockout_seconds', $seconds);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function lockoutKey(Request $request, string $email): string
    {
        return 'login-lockout:'.sha1($email.'|'.$request->ip());
    }

    private function lockoutRemaining(Request $request, string $email): int
    {
        $data = Cache::get($this->lockoutKey($request, $email));

        if (! is_array($data)) {
            return 0;
        }

        return max(0, (int) ($data['locked_until'] ?? 0) - now()->getTimestamp());
    }

    private function lockoutStrikes(Request $request, string $email): int
    {
        $data = Cache::get($this->lockoutKey($request, $email));

        return is_array($data) ? (int) ($data['strikes'] ?? 0) : 0;
    }

    private function lockoutMessage(int $strikes, int $seconds): string
    {
        if ($strikes === 1) {
            return "Your inputs are wrong, please try again after {$seconds}s.";
        }

        if ($strikes === 2) {
            return "Second wrong attempt, please try again after {$seconds}s.";
        }

        return "Too many attempts, please try again after {$seconds}s.";
    }

    /**
     * Record a failed attempt and return the lockout in seconds.
     * Each consecutive failure doubles the wait, up to the maximum.
     */
    private function recordFailedAttempt(Request $request, string $email): int
    {
        $key = $this->lockoutKey($request, $email);
        $data = Cache::get($key);
        $strikes = is_array($data) ? (int) ($data['strikes'] ?? 0) + 1 : 1;
        $seconds = min(self::LOCKOUT_BASE_SECONDS * (2 ** ($strikes - 1)), self::LOCKOUT_MAX_SECONDS);

        Cache::put(
            $key,
            ['strikes' => $strikes, 'locked_until' => now()->getTimestamp() + $seconds],
            now()->addSeconds($seconds)->addMinutes(10),
        );

        return $seconds;
    }

    /**
     * Keep only the newest sessions for an account. The current login is
     * written after the response, so room is left for it.
     */
    private function enforceSessionLimit(int|string $userId): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $keep = DB::table('sessions')
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->limit(max(self::MAX_SESSIONS_PER_USER - 1, 0))
            ->pluck('id');

        DB::table('sessions')
            ->where('user_id', $userId)
            ->whereNotIn('id', $keep)
            ->delete();
    }
}
