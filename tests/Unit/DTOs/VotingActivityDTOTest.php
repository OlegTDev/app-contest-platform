<?php

use App\DTOs\VotingActivityDTO;
use App\Models\ContestEntry;

describe('VotingActivityDTO', function () {
    it('can be created from contest entry', function () {
        $entry = ContestEntry::factory()->create([
            'title' => 'My Project',
            'description' => 'Project description',
            'author_name' => 'John Doe',
            'author_department' => 'IT Department',
            'votes_count' => 42,
            'fields_data' => ['key' => 'value'],
        ]);

        $dto = VotingActivityDTO::from($entry);

        expect($dto->id)->toBe($entry->id)
            ->and($dto->title)->toBe('My Project')
            ->and($dto->description)->toBe('Project description')
            ->and($dto->authorName)->toBe('John Doe')
            ->and($dto->authorDepartment)->toBe('IT Department')
            ->and($dto->votesCount)->toBe(42)
            ->and($dto->type)->toBe('voting')
            ->and($dto->isVoted)->toBeFalse()
            ->and($dto->isViewed)->toBeFalse();
    });

    it('marks entry as voted when ID is in userVoteEntryIds', function () {
        $entry = ContestEntry::factory()->create([
            'title' => 'Project',
            'votes_count' => 10,
        ]);

        $dto = VotingActivityDTO::from($entry, [1, $entry->id, 99]);

        expect($dto->isVoted)->toBeTrue();
    });

    it('marks entry as viewed when ID is in viewedEntryIds', function () {
        $entry = ContestEntry::factory()->create([
            'title' => 'Project',
            'votes_count' => 10,
        ]);

        $dto = VotingActivityDTO::from($entry, [], [1, $entry->id, 99]);

        expect($dto->isViewed)->toBeTrue();
    });

    it('is not marked as voted or viewed when IDs are not in arrays', function () {
        $entry = ContestEntry::factory()->create([
            'title' => 'Project',
            'votes_count' => 10,
        ]);

        $dto = VotingActivityDTO::from($entry, [99], [100]);

        expect($dto->isVoted)->toBeFalse()
            ->and($dto->isViewed)->toBeFalse();
    });

    it('converts to array with snake_case keys', function () {
        $entry = ContestEntry::factory()->create([
            'title' => 'Project',
            'description' => 'Desc',
            'author_name' => 'Author',
            'author_department' => 'Dept',
            'votes_count' => 15,
            'fields_data' => ['field' => 'data'],
        ]);

        $dto = VotingActivityDTO::from($entry);
        $array = $dto->toArray();

        expect($array)->toHaveKeys([
            'id', 'title', 'description', 'author_name', 'author_department',
            'votes_count', 'is_voted', 'is_viewed', 'fields_data',
            'media', 'created_at', 'type',
        ])
        ->and($array['votes_count'])->toBe(15)
        ->and($array['is_voted'])->toBeFalse()
        ->and($array['type'])->toBe('voting');
    });

    it('handles null fields_data', function () {
        $entry = ContestEntry::factory()->create([
            'title' => 'Project',
            'votes_count' => 10,
            'fields_data' => null,
        ]);

        $dto = VotingActivityDTO::from($entry);

        expect($dto->fieldsData)->toBeNull();
    });

    it('casts fields_data to array', function () {
        $entry = ContestEntry::factory()->create([
            'title' => 'Project',
            'votes_count' => 10,
            'fields_data' => ['key' => 'value', 'nested' => ['a' => 1]],
        ]);

        $dto = VotingActivityDTO::from($entry);

        expect($dto->fieldsData)->toBeArray()
            ->and($dto->fieldsData['key'])->toBe('value');
    });

    it('handles null author fields', function () {
        $entry = ContestEntry::factory()->create([
            'title' => 'Project',
            'author_name' => null,
            'author_department' => null,
            'votes_count' => 0,
        ]);

        $dto = VotingActivityDTO::from($entry);

        expect($dto->authorName)->toBeNull()
            ->and($dto->authorDepartment)->toBeNull();
    });

    it('can create a collection from entries', function () {
        $entry1 = ContestEntry::factory()->create(['title' => 'E1', 'votes_count' => 10]);
        $entry2 = ContestEntry::factory()->create(['title' => 'E2', 'votes_count' => 20]);
        $entries = collect([$entry1, $entry2]);

        $dtoCollection = VotingActivityDTO::collection($entries);

        expect($dtoCollection)->toHaveCount(2)
            ->and($dtoCollection[0]->votesCount)->toBe(10)
            ->and($dtoCollection[1]->votesCount)->toBe(20);
    });

    it('handles empty media collection', function () {
        $entry = ContestEntry::factory()->create([
            'title' => 'Project',
            'votes_count' => 10,
        ]);

        $dto = VotingActivityDTO::from($entry);

        expect($dto->media)->toBe([]);
    });
});
