<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use LdapRecord\Laravel\Testing\DirectoryEmulator;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

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
        // if ($this->app->environment('local')) {

        //     DirectoryEmulator::setup('default', [
        //         'database' => ':memory:',
        //     ]);

        //     if (! LdapUser::where('samaccountname', 'ivanov')->exists()) {
        //         LdapUser::create([
        //             'cn' => 'Иван Иванов',
        //             'samaccountname' => 'ivanov',
        //             'mail' => 'ivanov@company.local',
        //             'objectguid' => 'bf94db3b-31d8-4f2b-8a8b-aa45a8aa1f8a',
        //         ]);
        //     }
        // }
        $this->configureViews();
        $this->configureRateLimiting();
        Fortify::authenticateUsing(function(Request $request) {
            $request->validate([
                Fortify::username() => 'required|string',
                'password' => 'required|string',
            ]);

            $credentials = [
                'samaccountname' => $request->input(Fortify::username()),
                'password' => $request->password,
            ];

            if ($this->app->environment('local')) {
                if (Auth::attempt([
                    'email' => $credentials['samaccountname'],
                    'password' => $credentials['password'],
                ])) {
                    return Auth::user();
                }
                return null;
            }

            if (Auth::guard('ldap')->attempt($credentials)) {
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
