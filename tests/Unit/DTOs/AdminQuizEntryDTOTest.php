<?php

use App\DTOs\AdminQuizEntryDTO;

describe('AdminQuizEntryDTO', function () {
    it('can be created from array with all fields', function () {
        $data = [
            'id' => 1,
            'title' => 'Quiz Question',
            'description' => 'Question description',
            'question' => 'What is 2+2?',
            'options' => [
                ['id' => 'opt_1', 'text' => '3', 'is_correct' => false],
                ['id' => 'opt_2', 'text' => '4', 'is_correct' => true],
            ],
            'quiz_type' => 'single',
            'is_scheduled' => false,
            'is_visible' => true,
            'created_at' => '2024-01-01 10:00',
        ];

        $dto = AdminQuizEntryDTO::fromArray($data);

        expect($dto->id)->toBe(1)
            ->and($dto->type)->toBe('quiz')
            ->and($dto->title)->toBe('Quiz Question')
            ->and($dto->description)->toBe('Question description')
            ->and($dto->question)->toBe('What is 2+2?')
            ->and($dto->options)->toBe([
                ['id' => 'opt_1', 'text' => '3', 'is_correct' => false],
                ['id' => 'opt_2', 'text' => '4', 'is_correct' => true],
            ])
            ->and($dto->quizType)->toBe('single')
            ->and($dto->isScheduled)->toBeFalse()
            ->and($dto->isVisible)->toBeTrue()
            ->and($dto->createdAt)->toBe('2024-01-01 10:00');
    });

    it('uses default type of "quiz"', function () {
        $data = [
            'id' => 1,
            'title' => 'Test',
            'is_scheduled' => false,
            'is_visible' => true,
            'created_at' => '2024-01-01 00:00',
        ];

        $dto = AdminQuizEntryDTO::fromArray($data);

        expect($dto->type)->toBe('quiz');
    });

    it('uses default quiz_type of "single"', function () {
        $data = [
            'id' => 1,
            'title' => 'Test',
            'is_scheduled' => false,
            'is_visible' => true,
            'created_at' => '2024-01-01 00:00',
        ];

        $dto = AdminQuizEntryDTO::fromArray($data);

        expect($dto->quizType)->toBe('single');
    });

    it('uses default values for missing optional fields', function () {
        $data = [
            'id' => 1,
            'title' => 'Test',
            'created_at' => '2024-01-01 00:00',
        ];

        $dto = AdminQuizEntryDTO::fromArray($data);

        expect($dto->description)->toBeNull()
            ->and($dto->question)->toBeNull()
            ->and($dto->options)->toBe([])
            ->and($dto->isScheduled)->toBeFalse()
            ->and($dto->isVisible)->toBeTrue();
    });

    it('handles null description and question', function () {
        $data = [
            'id' => 1,
            'title' => 'Test',
            'description' => null,
            'question' => null,
            'options' => [],
            'quiz_type' => 'single',
            'is_scheduled' => false,
            'is_visible' => true,
            'created_at' => '2024-01-01 00:00',
        ];

        $dto = AdminQuizEntryDTO::fromArray($data);

        expect($dto->description)->toBeNull()
            ->and($dto->question)->toBeNull();
    });
});
