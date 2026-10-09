<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureViews();
        $this->configureRateLimiting();
        Fortify::authenticateUsing(function (Request $request) {
            $request->validate([
                Fortify::username() => 'required|string',
                'password' => 'required|string',
            ]);

            $credentials = [
                'samaccountname' => $request->input(Fortify::username()),
                'password' => $request->password,
            ];

            if (Auth::attempt([
                'email' => $credentials['samaccountname'],
                'password' => $credentials['password'],
            ])) {
                return Auth::user();
            }

            if ($this->app->environment('production') && Auth::guard('ldap')->attempt($credentials)) {
                $user = Auth::guard('ldap')->user();

                return $user;
            }

            return null;
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('auth/login', [
            'status' => $request->session()->get('status'),
        ]));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
