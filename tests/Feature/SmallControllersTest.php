<?php

use App\Models\User;

describe('AuthController', function () {
    it('aborts when jwt_secret is not configured', function () {
        // In test environment, JWT_SECRET is not set, so the JwtService aborts with 503
        $response = $this->get('/auth/callback');

        $response->assertStatus(503);
    });
});

describe('Admin\UserController', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->moderator = User::factory()->create(['role' => 'moderator']);
        $this->user = User::factory()->create(['role' => 'user']);
    });

    describe('index', function () {
        it('renders user list for admin', function () {
            User::factory()->count(5)->create();

            $response = $this->actingAs($this->admin)->get('/admin/users');

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('admin/users/Index'));
        });

        it('rejects moderator from user list (admin middleware)', function () {
            $response = $this->actingAs($this->moderator)->get('/admin/users');

            $response->assertStatus(403);
        });

        it('rejects regular user from user list', function () {
            $response = $this->actingAs($this->user)->get('/admin/users');

            $response->assertStatus(403);
        });

        it('includes pagination data', function () {
            User::factory()->count(20)->create();

            $response = $this->actingAs($this->admin)->get('/admin/users');

            $response->assertInertia(fn ($page) => $page->has('pagination'));
        });

        it('includes available roles', function () {
            $response = $this->actingAs($this->admin)->get('/admin/users');

            $response->assertInertia(fn ($page) => $page->has('available_roles'));
        });
    });

    describe('updateRole', function () {
        it('allows admin to update user role', function () {
            $target = User::factory()->create(['role' => 'user']);

            $response = $this->actingAs($this->admin)->patch("/admin/users/{$target->id}/role", [
                'role' => 'moderator',
            ]);

            $response->assertRedirect();
            expect($target->fresh()->role->value)->toBe('moderator');
        });

        it('allows admin to promote user to admin', function () {
            $target = User::factory()->create(['role' => 'user']);

            $response = $this->actingAs($this->admin)->patch("/admin/users/{$target->id}/role", [
                'role' => 'admin',
            ]);

            $response->assertRedirect();
            expect($target->fresh()->role->value)->toBe('admin');
        });

        it('rejects regular user from updating role', function () {
            $target = User::factory()->create(['role' => 'user']);

            $response = $this->actingAs($this->user)->patch("/admin/users/{$target->id}/role", [
                'role' => 'admin',
            ]);

            $response->assertStatus(403);
        });

        it('rejects moderator from updating role', function () {
            $target = User::factory()->create(['role' => 'user']);

            $response = $this->actingAs($this->moderator)->patch("/admin/users/{$target->id}/role", [
                'role' => 'admin',
            ]);

            $response->assertStatus(403);
        });
    });
});

describe('ProfileController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create(['role' => 'user']);
    });

    describe('edit', function () {
        it('renders profile page for authenticated user', function () {
            $response = $this->actingAs($this->user)->get('/settings/profile');

            $response->assertStatus(200)
                ->assertInertia(fn ($page) => $page->component('settings/profile'));
        });

        it('rejects guests from profile page', function () {
            $response = $this->get('/settings/profile');

            $response->assertRedirect();
        });
    });

    describe('update', function () {
        it('updates user profile with all required fields', function () {
            $response = $this->actingAs($this->user)->patch('/settings/profile', [
                'name' => 'Updated Name',
                'login' => $this->user->login,
                'email' => 'updated@example.com',
            ]);

            $response->assertRedirect();
            expect($this->user->fresh()->name)->toBe('Updated Name');
            expect($this->user->fresh()->email)->toBe('updated@example.com');
        });

        it('rejects guests from updating profile', function () {
            $response = $this->patch('/settings/profile', [
                'name' => 'Hacked',
                'login' => 'some_login',
                'email' => 'hacked@example.com',
            ]);

            $response->assertRedirect();
            expect($this->user->fresh()->name)->not()->toBe('Hacked');
        });

        it('validates email uniqueness', function () {
            $otherUser = User::factory()->create(['email' => 'unique@example.com']);

            $response = $this->actingAs($this->user)->patch('/settings/profile', [
                'name' => 'Valid Name',
                'login' => $this->user->login,
                'email' => 'unique@example.com',
            ]);

            $response->assertSessionHasErrors('email');
        });

        it('validates required fields', function () {
            $response = $this->actingAs($this->user)->patch('/settings/profile', [
                'name' => '',
            ]);

            $response->assertSessionHasErrors(['name', 'login', 'email']);
        });
    });
});

describe('SecurityController', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'role' => 'user',
            'password' => bcrypt('current-password'),
        ]);
    });

    describe('edit', function () {
        it('rejects guests from security page', function () {
            $response = $this->get('/settings/security');

            $response->assertRedirect();
        });

        // Note: edit() requires password confirmation via RequirePassword middleware,
        // which is complex to test in feature tests. The controller method exists
        // and renders 'settings/security' component when middleware passes.
    });
});
