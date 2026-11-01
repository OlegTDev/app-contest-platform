<?php

use App\Http\Requests\SubmitQuizAnswerRequest;

describe('SubmitQuizAnswerRequest', function () {
    describe('validation rules', function () {
        it('passes validation with single selected option', function () {
            $request = SubmitQuizAnswerRequest::create('POST', '/quiz/1/answers', [
                'selected_options' => ['option_a'],
            ]);

            expect($request->validate($request->rules()))->toBe([
                'selected_options' => ['option_a'],
            ]);
        });

        it('passes validation with multiple selected options', function () {
            $request = SubmitQuizAnswerRequest::create('POST', '/quiz/1/answers', [
                'selected_options' => ['option_a', 'option_b', 'option_c'],
            ]);

            $validated = $request->validate($request->rules());
            expect($validated['selected_options'])->toHaveCount(3);
        });

        it('fails validation when selected_options is missing', function () {
            $request = SubmitQuizAnswerRequest::create('POST', '/quiz/1/answers', []);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when selected_options is empty array', function () {
            $request = SubmitQuizAnswerRequest::create('POST', '/quiz/1/answers', [
                'selected_options' => [],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when selected_options is not an array', function () {
            $request = SubmitQuizAnswerRequest::create('POST', '/quiz/1/answers', [
                'selected_options' => 'option_a',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when selected_options contains non-string item', function () {
            $request = SubmitQuizAnswerRequest::create('POST', '/quiz/1/answers', [
                'selected_options' => [123, 'option_b'],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when selected_options item exceeds 255 characters', function () {
            $request = SubmitQuizAnswerRequest::create('POST', '/quiz/1/answers', [
                'selected_options' => [str_repeat('a', 256)],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with long option strings at max length', function () {
            $request = SubmitQuizAnswerRequest::create('POST', '/quiz/1/answers', [
                'selected_options' => [str_repeat('a', 255)],
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });
    });

    describe('authorization', function () {
        it('allows any authenticated user to authorize', function () {
            $request = SubmitQuizAnswerRequest::create('POST', '/quiz/1/answers', [
                'selected_options' => ['option_a'],
            ]);

            expect($request->authorize())->toBeTrue();
        });
    });
});
