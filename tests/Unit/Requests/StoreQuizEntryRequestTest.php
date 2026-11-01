<?php

use App\Http\Requests\StoreQuizEntryRequest;

describe('StoreQuizEntryRequest', function () {
    describe('validation rules', function () {
        it('passes validation with all fields', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
                'title' => 'Quiz Entry',
                'description' => 'Quiz description',
                'question' => 'What is 2+2?',
                'options' => [
                    ['text' => '3', 'is_correct' => false],
                    ['text' => '4', 'is_correct' => true],
                    ['text' => '5', 'is_correct' => false],
                ],
                'type' => 'single',
                'sort_order' => 1,
                'show_from' => '2025-01-01 00:00:00',
                'show_until' => '2025-12-31 23:59:59',
            ]);

            $validated = $request->validate($request->rules());

            expect($validated['title'])->toBe('Quiz Entry');
            expect($validated['description'])->toBe('Quiz description');
            expect($validated['question'])->toBe('What is 2+2?');
            expect($validated['type'])->toBe('single');
            expect($validated['sort_order'])->toBe(1);
            expect($validated['show_from'])->toBe('2025-01-01 00:00:00');
            expect($validated['show_until'])->toBe('2025-12-31 23:59:59');
            expect($validated['options'])->toHaveCount(3);
        });

        it('passes validation with multiple type', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
                'title' => 'Quiz Entry',
                'question' => 'Select all that apply',
                'options' => [
                    ['text' => 'A', 'is_correct' => true],
                    ['text' => 'B', 'is_correct' => true],
                    ['text' => 'C', 'is_correct' => false],
                ],
                'type' => 'multiple',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated['type'])->toBe('multiple');
        });

        it('passes validation with minimum fields', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('passes validation with nullable fields omitted', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('passes validation with null sort_order', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
                'sort_order' => null,
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('passes validation with null show dates', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
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

        it('fails validation when title is missing', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when question is missing', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
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

        it('fails validation when options are missing', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'type' => 'single',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when fewer than 2 options provided', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
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

        it('fails validation when exactly 2 options provided (minimum)', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => true],
                    ['text' => 'B', 'is_correct' => false],
                ],
                'type' => 'single',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when option text is missing', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
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
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
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

        it('passes validation when option text is at max length', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => str_repeat('a', 500), 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when is_correct is missing', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A'],
                    ['text' => 'B', 'is_correct' => false],
                ],
                'type' => 'single',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when is_correct is not a boolean', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
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
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
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
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
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
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
                'sort_order' => 'not-an-integer',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when show_from has invalid date format', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
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
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
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
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
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

        it('passes validation with both valid date format dates', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
                'title' => 'Quiz',
                'question' => 'Question?',
                'options' => [
                    ['text' => 'A', 'is_correct' => false],
                    ['text' => 'B', 'is_correct' => true],
                ],
                'type' => 'single',
                'show_from' => '2025-01-01 00:00:00',
                'show_until' => '2025-12-31 23:59:59',
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });
    });

    describe('authorization', function () {
        it('allows any authenticated user to authorize', function () {
            $request = StoreQuizEntryRequest::create('POST', '/contests/1/quiz-entries', [
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
