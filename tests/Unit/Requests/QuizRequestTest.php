<?php

use App\Http\Requests\QuizRequest;

describe('QuizRequest', function () {
    describe('validation rules', function () {
        it('passes validation with single question single type', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'My Quiz',
                'description' => 'Quiz description',
                'questions' => [
                    [
                        'question' => 'What is 2+2?',
                        'type' => 'single',
                        'correct_answer' => '4',
                        'options' => ['3', '4', '5'],
                    ],
                ],
            ]);

            $validated = $request->validate($request->rules());
            expect($validated['title'])->toBe('My Quiz');
            expect($validated['questions'])->toHaveCount(1);
        });

        it('passes validation with multiple questions', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Multi Quiz',
                'questions' => [
                    [
                        'question' => 'Question 1?',
                        'type' => 'single',
                        'correct_answer' => 'A',
                        'options' => ['A', 'B'],
                    ],
                    [
                        'question' => 'Question 2?',
                        'type' => 'multiple',
                        'correct_answer' => ['A', 'C'],
                        'options' => ['A', 'B', 'C'],
                    ],
                ],
            ]);

            $validated = $request->validate($request->rules());
            expect($validated['questions'])->toHaveCount(2);
        });

        it('passes validation with multiple type', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Multi Quiz',
                'questions' => [
                    [
                        'question' => 'Select all correct',
                        'type' => 'multiple',
                        'correct_answer' => ['A', 'B'],
                        'options' => ['A', 'B', 'C'],
                    ],
                ],
            ]);

            $validated = $request->validate($request->rules());
            expect($validated['questions'][0]['type'])->toBe('multiple');
        });

        it('passes validation with nullable description', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz Without Description',
                'questions' => [
                    [
                        'question' => 'Question?',
                        'type' => 'single',
                        'correct_answer' => 'A',
                        'options' => ['A', 'B'],
                    ],
                ],
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when title is missing', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'questions' => [
                    [
                        'question' => 'Question?',
                        'type' => 'single',
                        'correct_answer' => 'A',
                        'options' => ['A', 'B'],
                    ],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when questions are missing', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when questions is empty array', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
                'questions' => [],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when question text is missing', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
                'questions' => [
                    [
                        'type' => 'single',
                        'correct_answer' => 'A',
                        'options' => ['A', 'B'],
                    ],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when question type is missing', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
                'questions' => [
                    [
                        'question' => 'Question?',
                        'correct_answer' => 'A',
                        'options' => ['A', 'B'],
                    ],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when question type is invalid', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
                'questions' => [
                    [
                        'question' => 'Question?',
                        'type' => 'multiple_choice',
                        'correct_answer' => 'A',
                        'options' => ['A', 'B'],
                    ],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when correct_answer is missing', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
                'questions' => [
                    [
                        'question' => 'Question?',
                        'type' => 'single',
                        'options' => ['A', 'B'],
                    ],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when options are missing', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
                'questions' => [
                    [
                        'question' => 'Question?',
                        'type' => 'single',
                        'correct_answer' => 'A',
                    ],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when options item is missing', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
                'questions' => [
                    [
                        'question' => 'Question?',
                        'type' => 'single',
                        'correct_answer' => 'A',
                        'options' => ['A', null],
                    ],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when option text exceeds 255 characters', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
                'questions' => [
                    [
                        'question' => 'Question?',
                        'type' => 'single',
                        'correct_answer' => 'A',
                        'options' => [str_repeat('a', 256), 'B'],
                    ],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('fails validation when title exceeds 255 characters', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => str_repeat('a', 256),
                'questions' => [
                    [
                        'question' => 'Question?',
                        'type' => 'single',
                        'correct_answer' => 'A',
                        'options' => ['A', 'B'],
                    ],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation when title is at max length', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => str_repeat('a', 255),
                'questions' => [
                    [
                        'question' => 'Question?',
                        'type' => 'single',
                        'correct_answer' => 'A',
                        'options' => ['A', 'B'],
                    ],
                ],
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when description exceeds 1000 characters', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
                'description' => str_repeat('a', 1001),
                'questions' => [
                    [
                        'question' => 'Question?',
                        'type' => 'single',
                        'correct_answer' => 'A',
                        'options' => ['A', 'B'],
                    ],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation when description is at max length', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
                'description' => str_repeat('a', 1000),
                'questions' => [
                    [
                        'question' => 'Question?',
                        'type' => 'single',
                        'correct_answer' => 'A',
                        'options' => ['A', 'B'],
                    ],
                ],
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });

        it('fails validation when question text exceeds 1000 characters', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
                'questions' => [
                    [
                        'question' => str_repeat('a', 1001),
                        'type' => 'single',
                        'correct_answer' => 'A',
                        'options' => ['A', 'B'],
                    ],
                ],
            ]);

            expect(fn () => $request->validate($request->rules()))
                ->toThrow(\Illuminate\Validation\ValidationException::class);
        });

        it('passes validation when question text is at max length', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
                'questions' => [
                    [
                        'question' => str_repeat('a', 1000),
                        'type' => 'single',
                        'correct_answer' => 'A',
                        'options' => ['A', 'B'],
                    ],
                ],
            ]);

            $validated = $request->validate($request->rules());
            expect($validated)->toBeArray();
        });
    });

    describe('custom messages', function () {
        it('has custom messages defined', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', []);
            $messages = $request->messages();

            expect($messages)->toHaveKey('questions.min');
            expect($messages)->toHaveKey('questions.*.question.required');
            expect($messages)->toHaveKey('questions.*.type.required');
            expect($messages)->toHaveKey('questions.*.type.in');
            expect($messages)->toHaveKey('questions.*.correct_answer.required');
            expect($messages)->toHaveKey('questions.*.options.required');
            expect($messages)->toHaveKey('questions.*.options.*.required');
        });

        it('has correct question min message', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', []);
            expect($request->messages()['questions.min'])->toBe('Quiz must have at least one question.');
        });
    });

    describe('authorization', function () {
        it('allows any authenticated user to authorize', function () {
            $request = QuizRequest::create('POST', '/admin/quizzes', [
                'title' => 'Quiz',
                'questions' => [
                    [
                        'question' => 'Question?',
                        'type' => 'single',
                        'correct_answer' => 'A',
                        'options' => ['A', 'B'],
                    ],
                ],
            ]);

            expect($request->authorize())->toBeTrue();
        });
    });
});
