<?php

use App\DTOs\AdminContestDTO;
use App\DTOs\AdminContestAuthorDTO;

describe('AdminContestDTO', function () {
    it('can be created from array with all fields', function () {
        $data = [
            'id' => 1,
            'title' => 'Test Contest',
            'type' => 'voting',
            'status' => 'published',
            'description' => 'A test contest',
            'start_at' => '2024-01-01 10:00',
            'end_at' => '2024-01-15 18:00',
            'is_active' => true,
            'quiz_entry_count' => 5,
            'entry_count' => 10,
            'author' => [
                'id' => 42,
                'name' => 'John Doe',
            ],
            'can_manage' => true,
            'created_at' => '2024-01-01 09:00',
        ];

        $dto = AdminContestDTO::fromArray($data);

        expect($dto->id)->toBe(1)
            ->and($dto->title)->toBe('Test Contest')
            ->and($dto->type)->toBe('voting')
            ->and($dto->status)->toBe('published')
            ->and($dto->description)->toBe('A test contest')
            ->and($dto->startAt)->toBe('2024-01-01 10:00')
            ->and($dto->endAt)->toBe('2024-01-15 18:00')
            ->and($dto->isActive)->toBeTrue()
            ->and($dto->quizEntryCount)->toBe(5)
            ->and($dto->entryCount)->toBe(10)
            ->and($dto->canManage)->toBeTrue()
            ->and($dto->createdAt)->toBe('2024-01-01 09:00');
    });

    it('creates proper author DTO from array', function () {
        $data = [
            'id' => 1,
            'title' => 'Test',
            'type' => 'quiz',
            'status' => 'draft',
            'is_active' => false,
            'quiz_entry_count' => 3,
            'entry_count' => 0,
            'author' => [
                'id' => 99,
                'name' => 'Author Name',
            ],
            'can_manage' => false,
            'created_at' => '2024-06-01 12:00',
        ];

        $dto = AdminContestDTO::fromArray($data);

        expect($dto->author)->toBeInstanceOf(AdminContestAuthorDTO::class)
            ->and($dto->author->id)->toBe(99)
            ->and($dto->author->name)->toBe('Author Name');
    });

    it('uses default values for missing optional fields', function () {
        $data = [
            'id' => 1,
            'title' => 'Test',
            'type' => 'quiz',
            'status' => 'published',
            'is_active' => false,
            'author' => ['id' => 1, 'name' => 'Test'],
            'can_manage' => false,
            'created_at' => '2024-01-01 00:00',
        ];

        $dto = AdminContestDTO::fromArray($data);

        expect($dto->description)->toBeNull()
            ->and($dto->startAt)->toBeNull()
            ->and($dto->endAt)->toBeNull()
            ->and($dto->quizEntryCount)->toBe(0)
            ->and($dto->entryCount)->toBe(0);
    });

    it('converts to array with snake_case keys', function () {
        $dto = new AdminContestDTO(
            id: 1,
            title: 'Contest',
            type: 'voting',
            status: 'published',
            description: 'Desc',
            startAt: '2024-01-01 10:00',
            endAt: '2024-01-15 18:00',
            isActive: true,
            quizEntryCount: 5,
            entryCount: 10,
            author: new AdminContestAuthorDTO(1, 'Author'),
            canManage: true,
            createdAt: '2024-01-01 09:00',
        );

        $array = $dto->toArray();

        expect($array)->toHaveKeys([
            'id', 'title', 'type', 'status', 'description',
            'start_at', 'end_at', 'is_active', 'quiz_entry_count',
            'entry_count', 'author', 'can_manage', 'created_at',
        ])
        ->and($array['start_at'])->toBe('2024-01-01 10:00')
        ->and($array['is_active'])->toBeTrue()
        ->and($array['quiz_entry_count'])->toBe(5)
        ->and($array['author'])->toBe(['id' => 1, 'name' => 'Author']);
    });

    it('handles null description', function () {
        $data = [
            'id' => 1,
            'title' => 'Test',
            'type' => 'quiz',
            'status' => 'draft',
            'is_active' => false,
            'author' => ['id' => 1, 'name' => 'Test'],
            'can_manage' => false,
            'created_at' => '2024-01-01 00:00',
            'description' => null,
        ];

        $dto = AdminContestDTO::fromArray($data);

        expect($dto->description)->toBeNull();
    });

    it('handles missing description field', function () {
        $data = [
            'id' => 1,
            'title' => 'Test',
            'type' => 'quiz',
            'status' => 'draft',
            'is_active' => false,
            'author' => ['id' => 1, 'name' => 'Test'],
            'can_manage' => false,
            'created_at' => '2024-01-01 00:00',
        ];

        $dto = AdminContestDTO::fromArray($data);

        expect($dto->description)->toBeNull();
    });
});
