<?php

use App\Models\Contest;
use App\Models\QuizEntry;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->contest = Contest::factory()->create();
});

describe('QuizEntry model', function () {
    it('can create a quiz entry with all attributes', function () {
        $entry = QuizEntry::factory()->create([
            'contest_id' => $this->contest->id,
            'title' => 'Quiz Question',
            'description' => 'Question description',
            'sort_order' => 1,
            'show_from' => now()->subHour(),
            'show_until' => now()->addHour(),
        ]);

        expect($entry->title)->toBe('Quiz Question')
            ->and($entry->description)->toBe('Question description')
            ->and($entry->sort_order)->toBe(1)
            ->and($entry->show_from)->not->toBeNull()
            ->and($entry->show_until)->not->toBeNull();
    });

    it('has correct fillable attributes', function () {
        $entry = new QuizEntry;

        expect($entry->getFillable())
            ->toBe(['contest_id', 'title', 'description', 'fields_data', 'sort_order', 'show_from', 'show_until']);
    });

    it('casts fields_data as array', function () {
        $data = ['options' => ['a', 'b', 'c']];
        $entry = QuizEntry::factory()->create(['fields_data' => $data]);

        expect($entry->fields_data)->toBeArray()
            ->and($entry->fields_data)->toBe($data);
    });

    it('casts show_from and show_until as datetime', function () {
        $from = now()->subHour();
        $until = now()->addHour();
        $entry = QuizEntry::factory()->create([
            'show_from' => $from,
            'show_until' => $until,
        ]);

        expect($entry->show_from)->toBeInstanceOf(CarbonImmutable::class)
            ->and($entry->show_until)->toBeInstanceOf(CarbonImmutable::class);
    });

    describe('isVisible()', function () {
        it('returns true when no time restrictions', function () {
            $entry = QuizEntry::factory()->create([
                'show_from' => null,
                'show_until' => null,
            ]);

            expect($entry->isVisible())->toBeTrue();
        });

        it('returns true when within time range', function () {
            $entry = QuizEntry::factory()->create([
                'show_from' => now()->subHour(),
                'show_until' => now()->addHour(),
            ]);

            expect($entry->isVisible())->toBeTrue();
        });

        it('returns false when show_from is in the future', function () {
            $entry = QuizEntry::factory()->create([
                'show_from' => now()->addHour(),
                'show_until' => now()->addDays(2),
            ]);

            expect($entry->isVisible())->toBeFalse();
        });

        it('returns false when show_until is in the past', function () {
            $entry = QuizEntry::factory()->create([
                'show_from' => now()->subDays(2),
                'show_until' => now()->subHour(),
            ]);

            expect($entry->isVisible())->toBeFalse();
        });

        it('returns false when show_until equals show_from (zero window)', function () {
            $now = now();
            $entry = QuizEntry::factory()->create([
                'show_from' => $now,
                'show_until' => $now,
            ]);

            expect($entry->isVisible())->toBeFalse();
        });
    });

    describe('isScheduled()', function () {
        it('returns true when both show_from and show_until are set', function () {
            $entry = QuizEntry::factory()->create([
                'show_from' => now()->subHour(),
                'show_until' => now()->addHour(),
            ]);

            expect($entry->isScheduled())->toBeTrue();
        });

        it('returns false when show_from is null', function () {
            $entry = QuizEntry::factory()->create([
                'show_from' => null,
                'show_until' => now()->addHour(),
            ]);

            expect($entry->isScheduled())->toBeFalse();
        });

        it('returns false when show_until is null', function () {
            $entry = QuizEntry::factory()->create([
                'show_from' => now()->subHour(),
                'show_until' => null,
            ]);

            expect($entry->isScheduled())->toBeFalse();
        });

        it('returns false when both are null', function () {
            $entry = QuizEntry::factory()->create([
                'show_from' => null,
                'show_until' => null,
            ]);

            expect($entry->isScheduled())->toBeFalse();
        });
    });

    describe('relationships', function () {
        it('belongs to a contest', function () {
            $entry = QuizEntry::factory()->create(['contest_id' => $this->contest->id]);
            expect($entry->fresh('contest')->contest->id)->toBe($this->contest->id);
        });

        it('has many answers', function () {
            $entry = QuizEntry::factory()->create();
            $entry->answers()->createMany([
                ['user_id' => $this->user->id, 'answer' => 'Answer 1', 'is_correct' => true],
                ['user_id' => User::factory()->create()->id, 'answer' => 'Answer 2', 'is_correct' => false],
            ]);

            expect($entry->fresh('answers')->answers)->toHaveCount(2);
        });

        it('has many media files', function () {
            $entry = QuizEntry::factory()->create();
            $entry->media()->create([
                'contest_id' => $this->contest->id,
                'file_name' => 'question_image.png',
                'file_path' => 'quizzes/question_image.png',
                'file_type' => 'image/png',
            ]);

            expect($entry->fresh('media')->media)->toHaveCount(1);
        });
    });

    describe('edge cases', function () {
        it('handles null description', function () {
            $entry = QuizEntry::factory()->create(['description' => null]);
            expect($entry->description)->toBeNull();
        });

        it('handles null fields_data', function () {
            $entry = QuizEntry::factory()->create(['fields_data' => null]);
            expect($entry->fields_data)->toBeNull();
        });

        it('handles default sort_order of zero', function () {
            $entry = QuizEntry::factory()->create(['sort_order' => 0]);
            expect($entry->sort_order)->toBe(0);
        });
    });
});
