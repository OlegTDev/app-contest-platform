<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class UserService
{
    /**
     * Valid role values.
     *
     * @var list<array{value: string, label: string}>
     */
    private const AVAILABLE_ROLES = [
        ['value' => User::ROLE_ADMIN, 'label' => 'Admin'],
        ['value' => User::ROLE_MODERATOR, 'label' => 'Moderator'],
        ['value' => User::ROLE_USER, 'label' => 'User'],
    ];

    /**
     * Get paginated users for the admin index page.
     *
     * @return LengthAwarePaginator<int, UserDTO>
     */
    public function getPaginatedUsers(int $perPage = 15): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, UserDTO> $paginator */
        $paginator = User::query()
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->through(fn (User $user): UserDTO => UserDTO::from($user, self::AVAILABLE_ROLES));

        return $paginator;
    }

    /**
     * Update a user's role.
     */
    public function assignRole(User $user, string $role): User
    {
        $user->role = $role;
        $user->save();

        return $user;
    }
}
