<?php

use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;

describe('TwoFactorAuthenticationRequest', function () {
    describe('validation rules', function () {
        it('passes validation with empty data', function () {
            $request = TwoFactorAuthenticationRequest::create('POST', '/settings/two-factor', []);

            $validated = $request->validate($request->rules());
            expect($validated)->toBe([]);
        });

        it('has empty rules array', function () {
            $request = TwoFactorAuthenticationRequest::create('POST', '/settings/two-factor', []);
            expect($request->rules())->toBe([]);
        });

        it('has InteractsWithTwoFactorState trait methods', function () {
            $request = TwoFactorAuthenticationRequest::create('POST', '/settings/two-factor', []);
            expect(method_exists($request, 'ensureStateIsValid'))->toBeTrue();
        });
    });
});
