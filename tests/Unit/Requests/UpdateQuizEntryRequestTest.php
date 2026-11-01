<?php

use App\Http\Requests\UpdateQuizEntryRequest;

describe('UpdateQuizEntryRequest', function () {
    describe('validation rules', function () {
        it('passes validation with all fields', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Updated Quiz Entry',
                'description' => 'Updated description',
                'question' => 'Updated question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                    ['text' => 'C', 'is_correct' => false],
                ],
                'type' => 'multiple',
                'sort_order' => 5,
                'show_from' => '2025-06-01 00:00:00',
                'show_until' => '2025-12-31 23:59:59',
            ]);

            $validated = $request->validate($request->rules());

            expect($validated['title'])->toBe('Updated Quiz Entry');
            expect($validated['type'])->toBe('multiple');
            expect($validated['sort_order'])->toBe(5);
        });

        it('passes validation with minimal required fields', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Minimal Update',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated['title'])->toBe('Minimal Update');
        });

        it('fails validation when title is missing', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', []);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when title is empty string', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => '',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when title exceeds 255 characters', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => str_repeat('a', 256),
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with title at max length', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => str_repeat('a', 255),
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated['title'])->toBe(str_repeat('a', 255));
        });

        it('fails validation when question is missing', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when question exceeds 2000 characters', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => str_repeat('a', 2001),
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with question at max length', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => str_repeat('a', 2000),
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when fewer than 2 options provided', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => true],
                ],
                'type' => 'single',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when option text is missing', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['is_correct' => true],
                    ['text' => 'B', 'is_correct' => false],
                ],
                'type' => 'single',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when option text exceeds 500 characters', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => str_repeat('a', 501), 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when is_correct is not a boolean', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => 'true'],
                    ['text' => 'B', 'is_correct' => false],
                ],
                'type' => 'single',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when type is missing', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when type is invalid', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'multiple_choice',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when sort_order is not an integer', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
                'sort_order' => 'invalid',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with negative sort_order', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
                'sort_order' => -1,
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when show_from has invalid date format', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
                'show_from' => '01-01-2025',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with valid show_from date format', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
                'show_from' => '2025-01-01 00:00:00',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when show_until has invalid date format', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
                'show_until' => '2025/01/01',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation with null show dates', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
                'show_from' => null,
                'show_until' => null,
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });
    });

    describe('authorization', function () {
        it('allows any authenticated user to authorize', function () {
            $request = UpdateQuizEntryRequest::create('PATCH', '/contests/1/quiz-entries/1', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
            ]);

            expect($request->authorize())->toBeTrue();
        });
    });
});
