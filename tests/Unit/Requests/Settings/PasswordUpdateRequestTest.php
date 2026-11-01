<?php

use App\Http\Requests\Settings\PasswordUpdateRequest;

describe('PasswordUpdateRequest', function () {
    describe('validation rules', function () {
        it('has current_password and password rules', function () {
            $request = PasswordUpdateRequest::create('PATCH', '/settings/password', [
                'current_password' => 'CurrentPass1!',
                'password' => 'NewPass123!',
                'password_confirmation' => 'NewPass123!',
            ]);

            $rules = $request->rules();
            expect($rules)->toHaveKey('current_password');
            expect($rules)->toHaveKey('password');
        });

        it('requires current_password', function () {
            $request = PasswordUpdateRequest::create('PATCH', '/settings/password', [
                'password' => 'NewPass123!',
                'password_confirmation' => 'NewPass123!',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('requires password', function () {
            $request = PasswordUpdateRequest::create('PATCH', '/settings/password', [
                'current_password' => 'CurrentPass1!',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('requires password confirmation', function () {
            $request = PasswordUpdateRequest::create('PATCH', '/settings/password', [
                'current_password' => 'CurrentPass1!',
                'password' => 'NewPass123!',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails when password and confirmation do not match', function () {
            $request = PasswordUpdateRequest::create('PATCH', '/settings/password', [
                'current_password' => 'CurrentPass1!',
                'password' => 'NewPass123!',
                'password_confirmation' => 'DifferentPass!',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails when current_password is not a string', function () {
            $request = PasswordUpdateRequest::create('PATCH', '/settings/password', [
                'current_password' => 12345,
                'password' => 'NewPass123!',
                'password_confirmation' => 'NewPass123!',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails when password is not a string', function () {
            $request = PasswordUpdateRequest::create('PATCH', '/settings/password', [
                'current_password' => 'CurrentPass1!',
                'password' => 12345,
                'password_confirmation' => '12345',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });
    });
});
