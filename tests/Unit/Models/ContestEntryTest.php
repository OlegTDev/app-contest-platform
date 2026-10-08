<?php

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\ContestVote;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->contest = Contest::factory()->create();
});

describe('ContestEntry model', function () {
    it('can create an entry with all attributes', function () {
        $entry = ContestEntry::factory()->create([
            'contest_id' => $this->contest->id,
            'user_id' => $this->user->id,
            'title' => 'My Entry',
            'description' => 'Entry description',
            'author_name' => 'John Doe',
            'author_department' => 'Engineering',
            'votes_count' => 42,
        ]);

        expect($entry->title)->toBe('My Entry')
            ->and($entry->description)->toBe('Entry description')
            ->and($entry->author_name)->toBe('John Doe')
            ->and($entry->author_department)->toBe('Engineering')
            ->and($entry->votes_count)->toBe(42);
    });

    it('has correct fillable attributes', function () {
        $entry = new ContestEntry();

        expect($entry->getFillable())
            ->toBe(['contest_id', 'user_id', 'title', 'description', 'author_name', 'author_department', 'fields_data', 'votes_count']);
    });

    it('casts fields_data as array', function () {
        $data = ['key' => 'value', 'nested' => ['a' => 1]];
        $entry = ContestEntry::factory()->create(['fields_data' => $data]);

        expect($entry->fields_data)->toBeArray()
            ->and($entry->fields_data)->toBe($data);
    });

    it('casts votes_count as integer', function () {
        $entry = ContestEntry::factory()->create(['votes_count' => '5']);

        expect($entry->votes_count)->toBeInt()
            ->and($entry->votes_count)->toBe(5);
    });

    describe('isVisible()', function () {
        it('always returns true for voting entries', function () {
            $entry = ContestEntry::factory()->create();
            expect($entry->isVisible())->toBeTrue();
        });

        it('returns true for entries with null dates', function () {
            $entry = ContestEntry::factory()->create([
                'show_from' => null,
                'show_until' => null,
            ]);
            expect($entry->isVisible())->toBeTrue();
        });
    });

    describe('relationships', function () {
        it('belongs to a contest', function () {
            $entry = ContestEntry::factory()->create(['contest_id' => $this->contest->id]);
            expect($entry->fresh('contest')->contest->id)->toBe($this->contest->id);
        });

        it('belongs to a user', function () {
            $entry = ContestEntry::factory()->create(['user_id' => $this->user->id]);
            expect($entry->fresh('user')->user->id)->toBe($this->user->id);
        });

        it('has many votes', function () {
            $entry = ContestEntry::factory()->create();
            $entry->votes()->createMany([
                ['contest_id' => $this->contest->id, 'user_id' => $this->user->id],
                ['contest_id' => $this->contest->id, 'user_id' => User::factory()->create()->id],
            ]);

            expect($entry->fresh('votes')->votes)->toHaveCount(2);
        });

        it('has many media files', function () {
            $entry = ContestEntry::factory()->create();
            $entry->media()->createMany([
                ['contest_id' => $this->contest->id, 'file_name' => 'photo.jpg', 'file_path' => 'entries/photo.jpg', 'file_type' => 'image/jpeg'],
            ]);

            expect($entry->fresh('media')->media)->toHaveCount(1);
        });
    });

    describe('edge cases', function () {
        it('handles null description', function () {
            $entry = ContestEntry::factory()->create(['description' => null]);
            expect($entry->description)->toBeNull();
        });

        it('handles null author_name', function () {
            $entry = ContestEntry::factory()->create(['author_name' => null]);
            expect($entry->author_name)->toBeNull();
        });

        it('handles null author_department', function () {
            $entry = ContestEntry::factory()->create(['author_department' => null]);
            expect($entry->author_department)->toBeNull();
        });

        it('handles empty fields_data', function () {
            $entry = ContestEntry::factory()->create(['fields_data' => null]);
            expect($entry->fields_data)->toBeNull();
        });

        it('handles zero votes_count', function () {
            $entry = ContestEntry::factory()->create(['votes_count' => 0]);
            expect($entry->votes_count)->toBe(0);
        });
    });
});
