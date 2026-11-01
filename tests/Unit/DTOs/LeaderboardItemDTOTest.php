<?php

use App\DTOs\LeaderboardItemDTO;
use App\Models\ContestEntry;

describe('LeaderboardItemDTO', function () {
    it('can be created from contest entry', function () {
        $entry = ContestEntry::factory()->create([
            'title' => 'My Project',
            'author_name' => 'John Doe',
            'author_department' => 'IT Department',
            'votes_count' => 42,
        ]);

        $dto = LeaderboardItemDTO::from($entry);

        expect($dto->id)->toBe($entry->id)
            ->and($dto->title)->toBe('My Project')
            ->and($dto->votesCount)->toBe(42)
            ->and($dto->authorName)->toBe('John Doe')
            ->and($dto->authorDepartment)->toBe('IT Department');
    });

    it('converts to array with snake_case keys', function () {
        $entry = ContestEntry::factory()->create([
            'title' => 'Project',
            'author_name' => 'Author',
            'author_department' => 'Dept',
            'votes_count' => 10,
        ]);

        $dto = LeaderboardItemDTO::from($entry);
        $array = $dto->toArray();

        expect($array)->toHaveKeys(['id', 'title', 'votes_count', 'author_name', 'author_department'])
            ->and($array['votes_count'])->toBe(10)
            ->and($array['author_name'])->toBe('Author')
            ->and($array['author_department'])->toBe('Dept');
    });

    it('handles null author fields', function () {
        $entry = ContestEntry::factory()->create([
            'title' => 'Project',
            'author_name' => null,
            'author_department' => null,
            'votes_count' => 0,
        ]);

        $dto = LeaderboardItemDTO::from($entry);

        expect($dto->authorName)->toBeNull()
            ->and($dto->authorDepartment)->toBeNull();
    });

    it('can create a collection from entries', function () {
        $entry1 = ContestEntry::factory()->create(['title' => 'Entry 1', 'votes_count' => 10]);
        $entry2 = ContestEntry::factory()->create(['title' => 'Entry 2', 'votes_count' => 20]);
        $entries = collect([$entry1, $entry2]);

        $dtoCollection = LeaderboardItemDTO::collection($entries);

        expect($dtoCollection)->toHaveCount(2)
            ->and($dtoCollection[0]->votesCount)->toBe(10)
            ->and($dtoCollection[1]->votesCount)->toBe(20);
    });

    it('handles zero votes count', function () {
        $entry = ContestEntry::factory()->create(['votes_count' => 0]);

        $dto = LeaderboardItemDTO::from($entry);

        expect($dto->votesCount)->toBe(0);
    });
});
