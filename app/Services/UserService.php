<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

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

    public function findOrCreate(string $login, \stdClass $ldapAttributes): User
    {
        $mapAttributes = $this->mapAttributes($ldapAttributes);
        return User::firstOrCreate(['login' => $login], [
            ...$mapAttributes,
            'password' => Str::random(24),
            'domain' => 'default',
        ]);
    }

    /**
     * @param \stdClass $attributes
     * @return array{"city_code": string, department: string, email: string, name: string, phone: string, position: string}
     */
    private function mapAttributes(\stdClass $attributes): array
    {
        return [
            'name' => $attributes->cn,
            'email' => $attributes->mail,
            'department' => $attributes->department,
            'position' => $attributes->title,
            'city_code' => $attributes->postalCode,
            'phone' => $attributes->telephoneNumber,
        ];
    }
}
