<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;
use Illuminate\Support\Collection;

final readonly class UserDTO
{
    /**
     * @param  list<array{value: string, label: string}>  $availableRoles
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $role,
        public string $createdAt,
        public array $availableRoles = [],
    ) {}

    /**
     * Map a collection of users to DTOs.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, User>  $users
     * @param  list<array{value: string, label: string}>  $availableRoles
     * @return Collection<int, self>
     */
    public static function collection(
        Collection $users,
        array $availableRoles = [],
    ): Collection {
        return $users->map(fn (User $user): self => self::from($user, $availableRoles));
    }

    /**
     * @param  list<array{value: string, label: string}>  $availableRoles
     */
    public static function from(User $user, array $availableRoles = []): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            role: $user->role,
            createdAt: $user->created_at->format('Y-m-d H:i'),
            availableRoles: $availableRoles,
        );
    }
}
