<?php

use App\Http\Requests\AssignRoleRequest;
use App\Models\User;

describe('AssignRoleRequest', function () {
    describe('validation rules', function () {
        it('passes validation for admin role', function () {
            $request = AssignRoleRequest::create('PATCH', '/admin/users/1/role', [
                'role' => 'admin',
            ]);

            expect($request->validate($request->rules()))->toBe([
                'role' => 'admin',
            ]);
        });

        it('passes validation for moderator role', function () {
            $request = AssignRoleRequest::create('PATCH', '/admin/users/1/role', [
                'role' => 'moderator',
            ]);

            expect($request->validate($request->rules()))->toBe([
                'role' => 'moderator',
            ]);
        });

        it('passes validation for user role', function () {
            $request = AssignRoleRequest::create('PATCH', '/admin/users/1/role', [
                'role' => 'user',
            ]);

            expect($request->validate($request->rules()))->toBe([
                'role' => 'user',
            ]);
        });

        it('fails validation for invalid role', function () {
            $request = AssignRoleRequest::create('PATCH', '/admin/users/1/role', [
                'role' => 'superadmin',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation for missing role', function () {
            $request = AssignRoleRequest::create('PATCH', '/admin/users/1/role', []);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });
    });

    describe('authorization', function () {
        it('allows admin to authorize', function () {
            $admin = User::factory()->create(['role' => 'admin']);
            $request = AssignRoleRequest::create('PATCH', '/admin/users/1/role', ['role' => 'user']);
            $request->setUserResolver(fn () => $admin);

            expect($request->authorize())->toBeTrue();
        });

        it('rejects moderator from authorizing', function () {
            $moderator = User::factory()->create(['role' => 'moderator']);
            $request = AssignRoleRequest::create('PATCH', '/admin/users/1/role', ['role' => 'user']);
            $request->setUserResolver(fn () => $moderator);

            expect($request->authorize())->toBeFalse();
        });

        it('rejects regular user from authorizing', function () {
            $user = User::factory()->create(['role' => 'user']);
            $request = AssignRoleRequest::create('PATCH', '/admin/users/1/role', ['role' => 'user']);
            $request->setUserResolver(fn () => $user);

            expect($request->authorize())->toBeFalse();
        });

        it('throws error when no user is set', function () {
            $request = AssignRoleRequest::create('PATCH', '/admin/users/1/role', ['role' => 'user']);
            $request->setUserResolver(fn () => null);

            expect(fn () => $request->authorize())
                ->toThrow(\Error::class);
        });
    });
});
