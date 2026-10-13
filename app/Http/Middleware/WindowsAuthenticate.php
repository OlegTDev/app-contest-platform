<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class WindowsAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            return $next($request);
        }

        $authServerUrl = config('auth.windows.authServerUrl');
        $pingUrl = config('auth.windows.authServerUrl');
        $pingTimeout = config('auth.windows.pingTimeout');
        $callbackUrl = route('auth.callback');

        $isSlimAlive = false;

        try {
            $response = Http::timeout($pingTimeout)->get($pingUrl);
            if ($response->successful()) {
                $isSlimAlive = true;
            }
        } catch (\Exception $e) {
            $isSlimAlive = false;
        }

        if ($isSlimAlive) {

            $returnUrlEncoded = urlencode($callbackUrl);
            $redirectUrl = "{$authServerUrl}?return_url={$returnUrlEncoded}";

            if ($request->header('X-Inertia')) {
                return response('', 409)->header('X-Inertia-Location', $redirectUrl);
            }

            return redirect()->away($redirectUrl);
        }

        return redirect()->route('login');
    }
}
