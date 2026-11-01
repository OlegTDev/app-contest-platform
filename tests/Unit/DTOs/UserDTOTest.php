<?php

use App\DTOs\UserDTO;
use App\Enums\UserRole;
use App\Models\User;

describe('UserDTO', function () {
    it('can be created from user model', function () {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'login' => 'johndoe',
            'department' => 'IT Department',
            'position' => 'Developer',
            'city_code' => '12345',
            'phone' => '+79001234567',
            'role' => UserRole::USER->value,
        ]);

        $dto = UserDTO::from($user);

        expect($dto->id)->toBe($user->id)
            ->and($dto->name)->toBe('John Doe')
            ->and($dto->email)->toBe('john@example.com')
            ->and($dto->login)->toBe('johndoe')
            ->and($dto->department)->toBe('IT Department')
            ->and($dto->position)->toBe('Developer')
            ->and($dto->cityCode)->toBe('12345')
            ->and($dto->phone)->toBe('+79001234567')
            ->and($dto->role)->toBe(UserRole::USER->value);
    });

    it('handles null department, position, city_code, phone', function () {
        $user = User::factory()->create([
            'department' => null,
            'position' => null,
            'city_code' => null,
            'phone' => null,
        ]);

        $dto = UserDTO::from($user);

        expect($dto->department)->toBeNull()
            ->and($dto->position)->toBeNull()
            ->and($dto->cityCode)->toBeNull()
            ->and($dto->phone)->toBeNull();
    });

    it('handles role as string', function () {
        $user = User::factory()->create(['role' => 'admin']);

        $dto = UserDTO::from($user);

        expect($dto->role)->toBe('admin');
    });

    it('handles role as UserRole enum', function () {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);

        $dto = UserDTO::from($user);

        expect($dto->role)->toBe(UserRole::ADMIN->value);
    });

    it('includes available roles when provided', function () {
        $user = User::factory()->create();
        $roles = [
            ['value' => 'admin', 'label' => 'Admin'],
            ['value' => 'moderator', 'label' => 'Moderator'],
            ['value' => 'user', 'label' => 'User'],
        ];

        $dto = UserDTO::from($user, $roles);

        expect($dto->availableRoles)->toBe($roles);
    });

    it('converts to array with snake_case keys', function () {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'login' => 'testuser',
            'department' => 'IT',
            'position' => 'Dev',
            'city_code' => '111',
            'phone' => '+123',
            'role' => UserRole::USER->value,
        ]);

        $dto = UserDTO::from($user);
        $array = $dto->toArray();

        expect($array)->toHaveKeys([
            'id', 'name', 'email', 'login', 'department',
            'position', 'city_code', 'phone', 'role',
            'created_at', 'available_roles',
        ])
        ->and($array['city_code'])->toBe('111')
        ->and($array['role'])->toBe(UserRole::USER->value);
    });

    it('handles null created_at', function () {
        $user = User::factory()->create();

        $dto = UserDTO::from($user);

        expect($dto->createdAt)->toBeString();
    });

    it('can create a collection from users', function () {
        $user1 = User::factory()->create(['name' => 'User 1']);
        $user2 = User::factory()->create(['name' => 'User 2']);
        $users = collect([$user1, $user2]);

        $dtoCollection = UserDTO::collection($users);

        expect($dtoCollection)->toHaveCount(2)
            ->and($dtoCollection[0]->name)->toBe('User 1')
            ->and($dtoCollection[1]->name)->toBe('User 2');
    });
});
