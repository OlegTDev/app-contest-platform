<?php

use App\Models\Contest;
use App\Models\User;
use App\Enums\ContestType;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('Contest model', function () {
    it('can create a contest with all attributes', function () {
        $contest = Contest::factory()->create([
            'title' => 'Test Contest',
            'type' => ContestType::QUIZ,
            'status' => 'draft',
            'description' => 'A test contest',
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);

        expect($contest->title)->toBe('Test Contest')
            ->and($contest->type)->toBe(ContestType::QUIZ)
            ->and($contest->status)->toBe('draft')
            ->and($contest->description)->toBe('A test contest')
            ->and($contest->start_at)->not->toBeNull()
            ->and($contest->end_at)->not->toBeNull();
    });

    it('has correct fillable attributes', function () {
        $contest = new Contest();

        expect($contest->getFillable())
            ->toBe(['title', 'type', 'project_schema', 'status', 'description', 'start_at', 'end_at']);
    });

    describe('isActive()', function () {
        it('returns true when published and within time range', function () {
            $contest = Contest::factory()->create([
                'status' => 'published',
                'start_at' => now()->subDay(),
                'end_at' => now()->addDay(),
            ]);

            expect($contest->isActive())->toBeTrue();
        });

        it('returns true when published with no time restrictions', function () {
            $contest = Contest::factory()->create([
                'status' => 'published',
                'start_at' => null,
                'end_at' => null,
            ]);

            expect($contest->isActive())->toBeTrue();
        });

        it('returns false when status is not published', function () {
            $contest = Contest::factory()->create([
                'status' => 'draft',
                'start_at' => now()->subDay(),
                'end_at' => now()->addDay(),
            ]);

            expect($contest->isActive())->toBeFalse();
        });

        it('returns false when start_at is in the future', function () {
            $contest = Contest::factory()->create([
                'status' => 'published',
                'start_at' => now()->addDay(),
                'end_at' => now()->addDays(2),
            ]);

            expect($contest->isActive())->toBeFalse();
        });

        it('returns false when end_at is in the past', function () {
            $contest = Contest::factory()->create([
                'status' => 'published',
                'start_at' => now()->subDays(2),
                'end_at' => now()->subDay(),
            ]);

            expect($contest->isActive())->toBeFalse();
        });

        it('returns false when both start and end are outside range', function () {
            $contest = Contest::factory()->create([
                'status' => 'published',
                'start_at' => now()->addDay(),
                'end_at' => now()->subDay(),
            ]);

            expect($contest->isActive())->toBeFalse();
        });
    });

    describe('isDraft()', function () {
        it('returns true when status is draft', function () {
            $contest = Contest::factory()->create(['status' => 'draft']);
            expect($contest->isDraft())->toBeTrue();
        });

        it('returns false when status is not draft', function () {
            $contest = Contest::factory()->create(['status' => 'published']);
            expect($contest->isDraft())->toBeFalse();
        });
    });

    describe('isPublished()', function () {
        it('returns true when status is published', function () {
            $contest = Contest::factory()->create(['status' => 'published']);
            expect($contest->isPublished())->toBeTrue();
        });

        it('returns false when status is not published', function () {
            $contest = Contest::factory()->create(['status' => 'draft']);
            expect($contest->isPublished())->toBeFalse();
        });
    });

    describe('canEdit()', function () {
        it('returns true when user is the author', function () {
            $contest = Contest::factory()->create(['user_id' => $this->user->id]);
            expect($contest->canEdit($this->user))->toBeTrue();
        });

        it('returns false when user is not the author', function () {
            $otherUser = User::factory()->create();
            $contest = Contest::factory()->create(['user_id' => $this->user->id]);
            expect($contest->canEdit($otherUser))->toBeFalse();
        });

        it('returns false when user does not exist', function () {
            $contest = Contest::factory()->create();
            $guest = new User(['id' => null]);
            expect($contest->canEdit($guest))->toBeFalse();
        });
    });

    describe('relationships', function () {
        it('has many projects', function () {
            $contest = Contest::factory()->create();
            $contest->projects()->createMany([
                ['title' => 'Project 1', 'user_id' => $this->user->id, 'fields_data' => []],
                ['title' => 'Project 2', 'user_id' => $this->user->id, 'fields_data' => []],
            ]);

            expect($contest->fresh('projects')->projects)->toHaveCount(2);
        });

        it('has many quiz entries', function () {
            $contest = Contest::factory()->create();
            $contest->quizEntries()->createMany([
                ['title' => 'Question 1'],
                ['title' => 'Question 2'],
            ]);

            expect($contest->fresh('quizEntries')->quizEntries)->toHaveCount(2);
        });

        it('has many contest entries', function () {
            $contest = Contest::factory()->create();
            $contest->entries()->createMany([
                ['title' => 'Entry 1', 'user_id' => $this->user->id],
                ['title' => 'Entry 2', 'user_id' => $this->user->id],
            ]);

            expect($contest->fresh('entries')->entries)->toHaveCount(2);
        });

        it('has many media files', function () {
            $contest = Contest::factory()->create();
            $contest->media()->createMany([
                ['file_name' => 'file1.jpg', 'file_path' => 'media/file1.jpg', 'file_type' => 'image/jpeg'],
                ['file_name' => 'file2.pdf', 'file_path' => 'media/file2.pdf', 'file_type' => 'application/pdf'],
            ]);

            expect($contest->fresh('media')->media)->toHaveCount(2);
        });

        it('belongs to an author user', function () {
            $contest = Contest::factory()->create(['user_id' => $this->user->id]);
            expect($contest->fresh('author')->author->id)->toBe($this->user->id);
        });
    });

    describe('casts', function () {
        it('casts project_schema as array', function () {
            $schema = [['name' => 'field1', 'type' => 'text']];
            $contest = Contest::factory()->create(['project_schema' => $schema]);

            expect($contest->project_schema)->toBeArray()
                ->and($contest->project_schema)->toBe($schema);
        });

        it('casts type as ContestType enum', function () {
            $contest = Contest::factory()->create(['type' => ContestType::VOTING]);

            expect($contest->type)->toBe(ContestType::VOTING)
                ->and($contest->type->value)->toBe('voting');
        });

        it('casts start_at and end_at as datetime', function () {
            $start = now()->subDay();
            $end = now()->addDay();
            $contest = Contest::factory()->create([
                'start_at' => $start,
                'end_at' => $end,
            ]);

            expect($contest->start_at)->toBeInstanceOf(\Carbon\CarbonImmutable::class)
                ->and($contest->end_at)->toBeInstanceOf(\Carbon\CarbonImmutable::class);
        });
    });
});
