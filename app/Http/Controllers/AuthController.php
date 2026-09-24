<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
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

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            if (! Auth::user()->is_active) {
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

            return redirect()->intended(route($landingRouteName));
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
