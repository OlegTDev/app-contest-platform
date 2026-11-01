<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;

describe('AdminMiddleware', function () {
    it('allows admin users through', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $request = \Illuminate\Http\Request::create('/admin', 'GET');
        $request->setUser($admin);

        $response = (new \App\Http\Middleware\AdminMiddleware())->handle($request, fn ($r) => response('ok'));

        expect($response->getStatusCode())->toBe(200);
    });

    it('rejects moderator users', function () {
        $moderator = User::factory()->create(['role' => 'moderator']);
        $request = \Illuminate\Http\Request::create('/admin', 'GET');
        $request->setUser($moderator);

        $response = (new \App\Http\Middleware\AdminMiddleware())->handle($request, fn ($r) => response('ok'));

        expect($response->getStatusCode())->toBe(403);
    });

    it('rejects regular users', function () {
        $user = User::factory()->create(['role' => 'user']);
        $request = \Illuminate\Http\Request::create('/admin', 'GET');
        $request->setUser($user);

        $response = (new \App\Http\Middleware\AdminMiddleware())->handle($request, fn ($r) => response('ok'));

        expect($response->getStatusCode())->toBe(403);
    });

    it('rejects unauthenticated users', function () {
        $request = \Illuminate\Http\Request::create('/admin', 'GET');

        $response = (new \App\Http\Middleware\AdminMiddleware())->handle($request, fn ($r) => response('ok'));

        expect($response->getStatusCode())->toBe(302);
    });
});

describe('ModeratorMiddleware', function () {
    it('allows admin users through', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $request = \Illuminate\Http\Request::create('/admin/contests', 'GET');
        $request->setUser($admin);

        $response = (new \App\Http\Middleware\ModeratorMiddleware())->handle($request, fn ($r) => response('ok'));

        expect($response->getStatusCode())->toBe(200);
    });

    it('allows moderator users through', function () {
        $moderator = User::factory()->create(['role' => 'moderator']);
        $request = \Illuminate\Http\Request::create('/admin/contests', 'GET');
        $request->setUser($moderator);

        $response = (new \App\Http\Middleware\ModeratorMiddleware())->handle($request, fn ($r) => response('ok'));

        expect($response->getStatusCode())->toBe(200);
    });

    it('rejects regular users', function () {
        $user = User::factory()->create(['role' => 'user']);
        $request = \Illuminate\Http\Request::create('/admin/contests', 'GET');
        $request->setUser($user);

        $response = (new \App\Http\Middleware\ModeratorMiddleware())->handle($request, fn ($r) => response('ok'));

        expect($response->getStatusCode())->toBe(403);
    });

    it('rejects unauthenticated users', function () {
        $request = \Illuminate\Http\Request::create('/admin/contests', 'GET');

        $response = (new \App\Http\Middleware\ModeratorMiddleware())->handle($request, fn ($r) => response('ok'));

        expect($response->getStatusCode())->toBe(302);
    });
});
