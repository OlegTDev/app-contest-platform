<?php

use App\DTOs\UserDTO;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\UserService;

beforeEach(function () {
    $this->service = new UserService;
});

describe('UserService', function () {
    describe('getPaginatedUsers', function () {
        it('returns paginated users', function () {
            User::factory()->count(5)->create();

            $paginator = $this->service->getPaginatedUsers(10);

            expect($paginator->total())->toBe(5)
                ->and($paginator->perPage())->toBe(10)
                ->and($paginator->items())->toHaveCount(5);
        });

        it('returns UserDTO instances in items', function () {
            User::factory()->create(['name' => 'Test User']);

            $paginator = $this->service->getPaginatedUsers(10);

            expect($paginator->items()[0])->toBeInstanceOf(UserDTO::class)
                ->and($paginator->items()[0]->name)->toBe('Test User');
        });

        it('respects per page limit', function () {
            User::factory()->count(10)->create();

            $paginator = $this->service->getPaginatedUsers(3);

            expect($paginator->total())->toBe(10)
                ->and($paginator->perPage())->toBe(3)
                ->and(count($paginator->items()))->toBe(3);
        });

        it('orders users by created_at descending', function () {
            User::factory()->create(['name' => 'Old']);
            sleep(1);
            User::factory()->create(['name' => 'New']);

            $paginator = $this->service->getPaginatedUsers(10);
            $items = $paginator->items();

            expect($items[0]->name)->toBe('New')
                ->and($items[1]->name)->toBe('Old');
        });
    });

    describe('assignRole', function () {
        it('updates user role', function () {
            $user = User::factory()->create(['role' => UserRole::USER->value]);

            $updatedUser = $this->service->assignRole($user, UserRole::ADMIN->value);

            expect($updatedUser->role)->toHaveProperty('value', 'admin')
                ->and(User::find($user->id)->role)->toHaveProperty('value', 'admin');
        });

        it('returns the updated user', function () {
            $user = User::factory()->create();

            $result = $this->service->assignRole($user, UserRole::MODERATOR->value);

            expect($result)->toBe($user)
                ->and($result->fresh()->role)->toHaveProperty('value', 'moderator');
        });
    });

    describe('findOrCreate', function () {
        it('creates a new user when login does not exist', function () {
            $ldapAttributes = (object) [
                'cn' => 'John Doe',
                'mail' => 'john@example.com',
                'department' => 'IT',
                'title' => 'Developer',
                'postalCode' => '12345',
                'telephoneNumber' => '+79001234567',
            ];

            $user = $this->service->findOrCreate('johndoe', $ldapAttributes);

            expect($user->login)->toBe('johndoe')
                ->and($user->name)->toBe('John Doe')
                ->and($user->email)->toBe('john@example.com')
                ->and($user->department)->toBe('IT')
                ->and($user->position)->toBe('Developer')
                ->and($user->city_code)->toBe('12345')
                ->and($user->phone)->toBe('+79001234567')
                ->and($user->domain)->toBe('default')
                ->and(strlen($user->password))->toBeGreaterThan(0);
        });

        it('returns existing user when login exists', function () {
            $existingUser = User::factory()->create(['login' => 'existing']);

            $ldapAttributes = (object) [
                'cn' => 'Different Name',
                'mail' => 'different@example.com',
                'department' => 'HR',
                'title' => 'Manager',
                'postalCode' => '54321',
                'telephoneNumber' => '+79999999999',
            ];

            $user = $this->service->findOrCreate('existing', $ldapAttributes);

            expect($user->id)->toBe($existingUser->id)
                ->and($user->name)->not->toBe('Different Name');
        });
    });
});
