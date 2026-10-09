<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::clear('login');
});

describe('Local authentication by login', function () {
    it('authenticates user with correct login and password', function () {
        $user = User::factory()->create([
            'login' => 'testuser',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'testuser',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('contests.public', absolute: false));
    });

    it('fails to authenticate with incorrect login', function () {
        User::factory()->create([
            'login' => 'testuser',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'nonexistent',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    });

    it('fails to authenticate with correct login but wrong password', function () {
        $user = User::factory()->create([
            'login' => 'testuser',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'testuser',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    });

    it('is rate limited after too many failed login attempts', function () {
        $user = User::factory()->create([
            'login' => 'testuser',
        ]);

        for ($i = 0; $i < 6; $i++) {
            $this->post(route('login.store'), [
                'login' => 'testuser',
                'password' => 'wrong-password',
            ]);
        }

        $this->post(route('login.store'), [
            'login' => 'testuser',
            'password' => 'password',
        ])->assertStatus(429);
    });
});
