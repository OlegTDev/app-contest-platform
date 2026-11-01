<?php

use App\DTOs\AdminVotingEntryDTO;

describe('AdminVotingEntryDTO', function () {
    it('can be created from array with all fields', function () {
        $data = [
            'id' => 1,
            'title' => 'My Project',
            'description' => 'Project description',
            'author_name' => 'John Doe',
            'author_department' => 'IT Department',
            'votes_count' => 42,
            'created_at' => '2024-01-01 10:00',
        ];

        $dto = AdminVotingEntryDTO::fromArray($data);

        expect($dto->id)->toBe(1)
            ->and($dto->type)->toBe('voting')
            ->and($dto->title)->toBe('My Project')
            ->and($dto->description)->toBe('Project description')
            ->and($dto->authorName)->toBe('John Doe')
            ->and($dto->authorDepartment)->toBe('IT Department')
            ->and($dto->votesCount)->toBe(42)
            ->and($dto->createdAt)->toBe('2024-01-01 10:00');
    });

    it('uses default type of "voting"', function () {
        $data = [
            'id' => 1,
            'title' => 'Test',
            'created_at' => '2024-01-01 00:00',
        ];

        $dto = AdminVotingEntryDTO::fromArray($data);

        expect($dto->type)->toBe('voting');
    });

    it('uses default values for missing optional fields', function () {
        $data = [
            'id' => 1,
            'title' => 'Test',
            'created_at' => '2024-01-01 00:00',
        ];

        $dto = AdminVotingEntryDTO::fromArray($data);

        expect($dto->description)->toBeNull()
            ->and($dto->authorName)->toBeNull()
            ->and($dto->authorDepartment)->toBeNull()
            ->and($dto->votesCount)->toBe(0);
    });

    it('casts votes_count to integer', function () {
        $data = [
            'id' => 1,
            'title' => 'Test',
            'votes_count' => '15',
            'created_at' => '2024-01-01 00:00',
        ];

        $dto = AdminVotingEntryDTO::fromArray($data);

        expect($dto->votesCount)->toBe(15)
            ->and($dto->votesCount)->toBeInt();
    });

    it('handles null author fields', function () {
        $data = [
            'id' => 1,
            'title' => 'Test',
            'author_name' => null,
            'author_department' => null,
            'votes_count' => 0,
            'created_at' => '2024-01-01 00:00',
        ];

        $dto = AdminVotingEntryDTO::fromArray($data);

        expect($dto->authorName)->toBeNull()
            ->and($dto->authorDepartment)->toBeNull();
    });
});
