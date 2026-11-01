<?php

use App\DTOs\ContestShowDTO;

describe('ContestShowDTO', function () {
    it('can be created with all fields', function () {
        $dto = new ContestShowDTO(
            id: 1,
            title: 'Test Contest',
            type: 'voting',
            status: 'published',
            slug: 'test-contest',
            description: 'A test contest',
            startAt: '2024-01-01 10:00',
            endAt: '2024-01-15 18:00',
            isActive: true,
            isTimeActive: true,
            createdAt: '2024-01-01 09:00',
            isArchived: false,
            quizActivities: [],
            votingActivities: [],
            leaderboard: [],
            canManage: true,
        );

        expect($dto->id)->toBe(1)
            ->and($dto->title)->toBe('Test Contest')
            ->and($dto->type)->toBe('voting')
            ->and($dto->status)->toBe('published')
            ->and($dto->slug)->toBe('test-contest')
            ->and($dto->description)->toBe('A test contest')
            ->and($dto->startAt)->toBe('2024-01-01 10:00')
            ->and($dto->endAt)->toBe('2024-01-15 18:00')
            ->and($dto->isActive)->toBeTrue()
            ->and($dto->isTimeActive)->toBeTrue()
            ->and($dto->createdAt)->toBe('2024-01-01 09:00')
            ->and($dto->isArchived)->toBeFalse()
            ->and($dto->canManage)->toBeTrue();
    });

    it('has default values for optional fields', function () {
        $dto = new ContestShowDTO(
            id: 1,
            title: 'Test',
            type: 'quiz',
            status: 'draft',
            slug: null,
            description: null,
            startAt: null,
            endAt: null,
            isActive: false,
            isTimeActive: false,
            createdAt: '2024-01-01 00:00',
        );

        expect($dto->isArchived)->toBeFalse()
            ->and($dto->quizActivities)->toBe([])
            ->and($dto->votingActivities)->toBe([])
            ->and($dto->leaderboard)->toBe([])
            ->and($dto->canManage)->toBeFalse();
    });

    it('converts to array with snake_case keys', function () {
        $dto = new ContestShowDTO(
            id: 1,
            title: 'Contest',
            type: 'voting',
            status: 'published',
            slug: 'contest',
            description: 'Desc',
            startAt: '2024-01-01 10:00',
            endAt: '2024-01-15 18:00',
            isActive: true,
            isTimeActive: true,
            createdAt: '2024-01-01 09:00',
            isArchived: true,
            quizActivities: [
                ['id' => 1, 'type' => 'quiz', 'title' => 'Q1', 'description' => null, 'question' => 'What?', 'options' => [], 'quiz_type' => 'single', 'is_scheduled' => false, 'is_visible' => true, 'media' => [], 'created_at' => '2024-01-01 00:00'],
            ],
            votingActivities: [
                ['id' => 1, 'title' => 'E1', 'description' => null, 'author_name' => 'Author', 'author_department' => 'Dept', 'votes_count' => 10, 'is_voted' => false, 'fields_data' => null, 'media' => [], 'created_at' => '2024-01-01 00:00'],
            ],
            leaderboard: [
                ['id' => 1, 'title' => 'E1', 'votes_count' => 10, 'author_name' => 'Author'],
            ],
            canManage: true,
        );

        $array = $dto->toArray();

        expect($array)->toHaveKeys([
            'id', 'title', 'type', 'status', 'slug', 'description',
            'start_at', 'end_at', 'is_active', 'is_time_active',
            'is_archived', 'activities', 'leaderboard', 'can_manage', 'created_at',
        ])
        ->and($array['start_at'])->toBe('2024-01-01 10:00')
        ->and($array['is_active'])->toBeTrue()
        ->and($array['is_time_active'])->toBeTrue()
        ->and($array['is_archived'])->toBeTrue()
        ->and($array['can_manage'])->toBeTrue()
        ->and($array['activities'])->toHaveCount(2)
        ->and($array['leaderboard'])->toHaveCount(1);
    });

    it('merges quiz and voting activities into activities', function () {
        $quizActivities = [
            ['id' => 1, 'type' => 'quiz', 'title' => 'Q1', 'description' => null, 'question' => null, 'options' => [], 'quiz_type' => 'single', 'is_scheduled' => false, 'is_visible' => true, 'media' => [], 'created_at' => '2024-01-01 00:00'],
        ];
        $votingActivities = [
            ['id' => 1, 'title' => 'E1', 'description' => null, 'author_name' => null, 'author_department' => null, 'votes_count' => 0, 'is_voted' => false, 'fields_data' => null, 'media' => [], 'created_at' => '2024-01-01 00:00'],
        ];

        $dto = new ContestShowDTO(
            id: 1, title: 'Test', type: 'voting', status: 'published',
            slug: null, description: null, startAt: null, endAt: null,
            isActive: false, isTimeActive: false, createdAt: '2024-01-01 00:00',
            quizActivities: $quizActivities, votingActivities: $votingActivities,
        );

        $array = $dto->toArray();

        expect($array['activities'])->toHaveCount(2)
            ->and($array['activities'][0]['type'])->toBe('quiz')
            ->and($array['activities'][1]['title'])->toBe('E1');
    });

    it('handles null slug and description', function () {
        $dto = new ContestShowDTO(
            id: 1, title: 'Test', type: 'quiz', status: 'draft',
            slug: null, description: null, startAt: null, endAt: null,
            isActive: false, isTimeActive: false, createdAt: '2024-01-01 00:00',
        );

        $array = $dto->toArray();

        expect($array['slug'])->toBeNull()
            ->and($array['description'])->toBeNull();
    });

    it('handles empty activities arrays', function () {
        $dto = new ContestShowDTO(
            id: 1, title: 'Test', type: 'quiz', status: 'published',
            slug: 'test', description: 'Desc', startAt: '2024-01-01 10:00',
            endAt: '2024-01-15 18:00', isActive: true, isTimeActive: true,
            createdAt: '2024-01-01 09:00', quizActivities: [], votingActivities: [],
        );

        $array = $dto->toArray();

        expect($array['activities'])->toBe([]);
    });
});
