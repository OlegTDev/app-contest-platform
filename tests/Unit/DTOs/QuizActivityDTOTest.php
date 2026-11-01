<?php

use App\DTOs\QuizActivityDTO;
use App\Models\QuizEntry;

describe('QuizActivityDTO', function () {
    it('can be created from quiz entry', function () {
        $entry = QuizEntry::factory()->create([
            'title' => 'Quiz Question',
            'description' => 'Question description',
            'fields_data' => [
                'question' => 'What is 2+2?',
                'options' => [
                    ['id' => 'opt_1', 'text' => '3', 'is_correct' => false],
                    ['id' => 'opt_2', 'text' => '4', 'is_correct' => true],
                ],
                'type' => 'single',
            ],
        ]);

        $dto = QuizActivityDTO::from($entry);

        expect($dto->id)->toBe($entry->id)
            ->and($dto->title)->toBe('Quiz Question')
            ->and($dto->description)->toBe('Question description')
            ->and($dto->question)->toBe('What is 2+2?')
            ->and($dto->options)->toBe([
                ['id' => 'opt_1', 'text' => '3', 'is_correct' => false],
                ['id' => 'opt_2', 'text' => '4', 'is_correct' => true],
            ])
            ->and($dto->quizType)->toBe('single')
            ->and($dto->type)->toBe('quiz')
            ->and($dto->isAnswered)->toBeFalse();
    });

    it('marks entry as answered when ID is in answeredEntryIds', function () {
        $entry = QuizEntry::factory()->create([
            'title' => 'Question',
            'fields_data' => [
                'options' => [['id' => 'opt_1', 'text' => 'A', 'is_correct' => true]],
                'type' => 'single',
            ],
        ]);

        $dto = QuizActivityDTO::from($entry, [1, $entry->id, 99]);

        expect($dto->isAnswered)->toBeTrue();
    });

    it('is not marked as answered when ID is not in answeredEntryIds', function () {
        $entry = QuizEntry::factory()->create([
            'title' => 'Question',
            'fields_data' => [
                'options' => [['id' => 'opt_1', 'text' => 'A', 'is_correct' => true]],
                'type' => 'single',
            ],
        ]);

        $dto = QuizActivityDTO::from($entry, [99, 100]);

        expect($dto->isAnswered)->toBeFalse();
    });

    it('converts to array with snake_case keys', function () {
        $entry = QuizEntry::factory()->create([
            'title' => 'Question',
            'description' => 'Desc',
            'fields_data' => [
                'question' => 'What?',
                'options' => [['id' => 'opt_1', 'text' => 'A', 'is_correct' => true]],
                'type' => 'multiple',
            ],
        ]);

        $dto = QuizActivityDTO::from($entry);
        $array = $dto->toArray();

        expect($array)->toHaveKeys([
            'id', 'title', 'description', 'question', 'options',
            'quizType', 'is_visible', 'is_answered', 'media',
            'created_at', 'type',
        ])
        ->and($array['quizType'])->toBe('multiple')
        ->and($array['is_answered'])->toBeFalse()
        ->and($array['type'])->toBe('quiz');
    });

    it('handles null description', function () {
        $entry = QuizEntry::factory()->create([
            'title' => 'Question',
            'description' => null,
            'fields_data' => [
                'options' => [['id' => 'opt_1', 'text' => 'A', 'is_correct' => true]],
                'type' => 'single',
            ],
        ]);

        $dto = QuizActivityDTO::from($entry);

        expect($dto->description)->toBeNull();
    });

    it('handles null options', function () {
        $entry = QuizEntry::factory()->create([
            'title' => 'Question',
            'fields_data' => [
                'options' => null,
                'type' => 'single',
            ],
        ]);

        $dto = QuizActivityDTO::from($entry);

        expect($dto->options)->toBeNull();
    });

    it('can create a collection from entries', function () {
        $entry1 = QuizEntry::factory()->create(['title' => 'Q1']);
        $entry2 = QuizEntry::factory()->create(['title' => 'Q2']);
        $entries = collect([$entry1, $entry2]);

        $dtoCollection = QuizActivityDTO::collection($entries);

        expect($dtoCollection)->toHaveCount(2)
            ->and($dtoCollection[0]->title)->toBe('Q1')
            ->and($dtoCollection[1]->title)->toBe('Q2');
    });

    it('handles empty media collection', function () {
        $entry = QuizEntry::factory()->create([
            'title' => 'Question',
            'fields_data' => [
                'options' => [['id' => 'opt_1', 'text' => 'A', 'is_correct' => true]],
                'type' => 'single',
            ],
        ]);

        $dto = QuizActivityDTO::from($entry);

        expect($dto->media)->toBe([]);
    });
});
