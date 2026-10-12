<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\User;
use Illuminate\Http\Response;

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

    /**
     * Проверить, может ли пользователь управлять конкурсом.
     * Админ и модератор имеют доступ ко всем конкурсам.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    protected function canManageContest(Contest $contest): void
    {
        $user = $this->user();

        if ($user->isAdmin() || $user->isModerator()) {
            return;
        }

        if ($user->id !== $contest->user_id) {
            abort(403, 'You do not have permission to manage this contest.');
        }
    }
}
