<?php

namespace App\Http\Controllers;

use App\Service\JwtService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function callback(Request $request, JwtService $jwtService, UserService $userService): RedirectResponse
    {
        $token = $request->post('token');

        if (!$token) {
            abort(401, 'Токен авторизации отсутствует');
        }

        $decoded = $jwtService->decode($token);
        $user = $userService->findOrCreate($decoded->login, $decoded);

        Auth::login($user, $remember = true);

        return back();
    }
}

