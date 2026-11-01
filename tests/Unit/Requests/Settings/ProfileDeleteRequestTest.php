<?php

use App\Http\Requests\Settings\ProfileDeleteRequest;

describe('ProfileDeleteRequest', function () {
    describe('validation rules', function () {
        it('has password rule', function () {
            $request = ProfileDeleteRequest::create('DELETE', '/settings/profile', [
                'password' => 'SomePassword1!',
            ]);

            $rules = $request->rules();
            expect($rules)->toHaveKey('password');
        });

        it('requires password', function () {
            $request = ProfileDeleteRequest::create('DELETE', '/settings/profile', []);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails when password is not a string', function () {
            $request = ProfileDeleteRequest::create('DELETE', '/settings/profile', [
                'password' => 12345,
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails when password is empty string', function () {
            $request = ProfileDeleteRequest::create('DELETE', '/settings/profile', [
                'password' => '',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });
    });
});
