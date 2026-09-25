<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('admin-login', function (Request $request) {
            $email = $request->input('email');
            $key = hash('sha256', strtolower(is_string($email) ? $email : '').'|'.$request->ip());

            return [
                Limit::perMinute(5)->by('login-account:'.$key),
                Limit::perMinute(30)->by('login-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('public-submissions', fn (Request $request) => Limit::perMinute(5)->by('public:'.$request->ip()));
    }
}
