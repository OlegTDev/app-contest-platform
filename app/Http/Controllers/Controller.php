<?php

namespace App\Http\Controllers;

use App\Models\User;

abstract class Controller
{
    /**
     * Получить текущего авторизованного пользователя.
     */
    protected function user(): User
    {
        $user = request()->user();

        if (! $user instanceof User) {
            abort(401, 'User not authenticated.');
        }

        return $user;
    }
}
