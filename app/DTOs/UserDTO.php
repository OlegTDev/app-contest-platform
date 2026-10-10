<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class UserDTO implements Arrayable
{
    /**
     * @param  list<array{value: string, label: string}>  $availableRoles
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $login,
        public ?string $department,
        public ?string $position,
        public ?string $cityCode,
        public ?string $phone,
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
            login: $user->login ?? '',
            department: $user->department,
            position: $user->position,
            cityCode: $user->city_code,
            phone: $user->phone,
            role: $user->role,
            createdAt: $user->created_at?->format('Y-m-d H:i') ?? '',
            availableRoles: $availableRoles,
        );
    }

    /**
     * Convert DTO to array with snake_case keys for frontend compatibility.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'login' => $this->login,
            'department' => $this->department,
            'position' => $this->position,
            'city_code' => $this->cityCode,
            'phone' => $this->phone,
            'role' => $this->role,
            'created_at' => $this->createdAt,
            'available_roles' => $this->availableRoles,
        ];
    }
}
