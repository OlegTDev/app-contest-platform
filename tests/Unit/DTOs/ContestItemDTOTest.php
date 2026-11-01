<?php

use App\DTOs\ContestItemDTO;
use App\Enums\ContestType;
use App\Models\Contest;
use App\Models\User;

describe('ContestItemDTO', function () {
    it('can be created from contest model', function () {
        $user = User::factory()->create();
        $contest = Contest::factory()->create([
            'title' => 'Test Contest',
            'type' => ContestType::VOTING,
            'slug' => 'test-contest',
            'description' => 'A test contest',
            'status' => 'published',
            'user_id' => $user->id,
        ]);

        $dto = ContestItemDTO::from($contest);

        expect($dto->id)->toBe($contest->id)
            ->and($dto->title)->toBe('Test Contest')
            ->and($dto->type)->toBe('voting')
            ->and($dto->slug)->toBe('test-contest')
            ->and($dto->description)->toBe('A test contest')
            ->and($dto->quizEntryCount)->toBe(0)
            ->and($dto->entryCount)->toBe(0)
            ->and($dto->isArchived)->toBeFalse();
    });

    it('marks contest as archived when status is archived', function () {
        $user = User::factory()->create();
        $contest = Contest::factory()->create(['status' => 'archived', 'user_id' => $user->id]);

        $dto = ContestItemDTO::from($contest);

        expect($dto->isArchived)->toBeTrue();
    });

    it('includes contest types when provided', function () {
        $user = User::factory()->create();
        $contest = Contest::factory()->create(['user_id' => $user->id]);
        $types = [
            ['value' => 'voting', 'label' => 'Voting'],
            ['value' => 'quiz', 'label' => 'Quiz'],
        ];

        $dto = ContestItemDTO::from($contest, $types);

        expect($dto->contestTypes)->toBe($types);
    });

    it('converts to array with snake_case keys', function () {
        $user = User::factory()->create();
        $contest = Contest::factory()->create([
            'title' => 'Contest',
            'type' => ContestType::QUIZ,
            'slug' => 'quiz-contest',
            'description' => 'Desc',
            'status' => 'published',
            'user_id' => $user->id,
        ]);

        $dto = ContestItemDTO::from($contest, []);

        $array = $dto->toArray();

        expect($array)->toHaveKeys([
            'id', 'title', 'type', 'slug', 'description',
            'start_at', 'end_at', 'quiz_entry_count',
            'entry_count', 'created_at', 'is_archived', 'contest_types',
        ])
        ->and($array['type'])->toBe('quiz')
        ->and($array['quiz_entry_count'])->toBe(0)
        ->and($array['is_archived'])->toBeFalse()
        ->and($array['contest_types'])->toBe([]);
    });

    it('formats dates correctly', function () {
        $user = User::factory()->create();
        $contest = Contest::factory()->create([
            'start_at' => \Carbon\CarbonImmutable::parse('2024-01-01 10:00:00'),
            'end_at' => \Carbon\CarbonImmutable::parse('2024-01-15 18:00:00'),
            'user_id' => $user->id,
        ]);

        $dto = ContestItemDTO::from($contest);

        expect($dto->startAt)->toBe('2024-01-01 10:00')
            ->and($dto->endAt)->toBe('2024-01-15 18:00')
            ->and($dto->createdAt)->toBeString();
    });

    it('handles null start_at and end_at', function () {
        $user = User::factory()->create();
        $contest = Contest::factory()->create([
            'start_at' => null,
            'end_at' => null,
            'user_id' => $user->id,
        ]);

        $dto = ContestItemDTO::from($contest);

        expect($dto->startAt)->toBeNull()
            ->and($dto->endAt)->toBeNull();
    });

    it('can create a collection from contests', function () {
        $user = User::factory()->create();
        $contest1 = Contest::factory()->create(['title' => 'Contest 1', 'user_id' => $user->id]);
        $contest2 = Contest::factory()->create(['title' => 'Contest 2', 'user_id' => $user->id]);
        $contests = collect([$contest1, $contest2]);

        $dtoCollection = ContestItemDTO::collection($contests);

        expect($dtoCollection)->toHaveCount(2)
            ->and($dtoCollection[0]->title)->toBe('Contest 1')
            ->and($dtoCollection[1]->title)->toBe('Contest 2');
    });

    it('handles contest with entries and quiz entries', function () {
        $user = User::factory()->create();
        $contest = Contest::factory()->create(['user_id' => $user->id]);
        $contest->entries()->createMany([
            ['title' => 'Entry 1', 'user_id' => $user->id],
            ['title' => 'Entry 2', 'user_id' => $user->id],
        ]);
        $contest->quizEntries()->createMany([
            ['title' => 'Quiz 1'],
            ['title' => 'Quiz 2'],
            ['title' => 'Quiz 3'],
        ]);

        $dto = ContestItemDTO::from($contest);

        expect($dto->entryCount)->toBe(2)
            ->and($dto->quizEntryCount)->toBe(3);
    });
});
