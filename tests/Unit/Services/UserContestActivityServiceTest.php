<?php

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\QuizAnswer;
use App\Models\QuizEntry;
use App\Models\User;
use App\Services\UserContestActivityService;

beforeEach(function () {
    $this->service = new UserContestActivityService;
    $this->user = User::factory()->create();
    $this->contest = Contest::factory()->create();
});

describe('UserContestActivityService', function () {
    describe('getUserVoteEntryIds', function () {
        it('returns empty collection when user has no votes', function () {
            $result = $this->service->getUserVoteEntryIds($this->user, $this->contest->id);

            expect($result)->toHaveCount(0);
        });

        it('returns voted entry IDs for the contest', function () {
            $entry1 = ContestEntry::factory()->create(['contest_id' => $this->contest->id, 'user_id' => $this->user->id]);
            $entry2 = ContestEntry::factory()->create(['contest_id' => $this->contest->id, 'user_id' => $this->user->id]);
            $otherContestEntry = ContestEntry::factory()->create(['user_id' => $this->user->id]);

            $this->user->votes()->create(['contest_id' => $this->contest->id, 'entry_id' => $entry1->id]);
            $this->user->votes()->create(['contest_id' => $this->contest->id, 'entry_id' => $entry2->id]);
            $this->user->votes()->create(['contest_id' => $otherContestEntry->contest_id, 'entry_id' => $otherContestEntry->id]);

            $result = $this->service->getUserVoteEntryIds($this->user, $this->contest->id);

            expect($result)->toHaveCount(2)
                ->and($result->contains($entry1->id))->toBeTrue()
                ->and($result->contains($entry2->id))->toBeTrue();
        });
    });

    describe('getViewedEntryIds', function () {
        it('returns empty collection when user has no views', function () {
            $result = $this->service->getViewedEntryIds($this->user, $this->contest->id);

            expect($result)->toHaveCount(0);
        });

        it('returns viewed entry IDs for the contest', function () {
            $entry1 = ContestEntry::factory()->create(['contest_id' => $this->contest->id, 'user_id' => $this->user->id]);
            $entry2 = ContestEntry::factory()->create(['contest_id' => $this->contest->id, 'user_id' => $this->user->id]);

            $this->user->entryViews()->create(['contest_id' => $this->contest->id, 'entry_id' => $entry1->id]);
            $this->user->entryViews()->create(['contest_id' => $this->contest->id, 'entry_id' => $entry2->id]);

            $result = $this->service->getViewedEntryIds($this->user, $this->contest->id);

            expect($result)->toHaveCount(2)
                ->and($result->contains($entry1->id))->toBeTrue()
                ->and($result->contains($entry2->id))->toBeTrue();
        });
    });

    describe('markEntryAsViewed', function () {
        it('creates a new view record', function () {
            $entry = ContestEntry::factory()->create(['contest_id' => $this->contest->id, 'user_id' => $this->user->id]);

            $this->service->markEntryAsViewed($this->user, $this->contest->id, $entry->id);

            expect($this->user->entryViews()->where('contest_id', $this->contest->id)
                ->where('entry_id', $entry->id)->count())->toBe(1);
        });

        it('updates existing view record', function () {
            $entry = ContestEntry::factory()->create(['contest_id' => $this->contest->id, 'user_id' => $this->user->id]);

            $this->user->entryViews()->create([
                'contest_id' => $this->contest->id,
                'entry_id' => $entry->id,
            ]);

            $this->service->markEntryAsViewed($this->user, $this->contest->id, $entry->id);

            expect($this->user->entryViews()->where('contest_id', $this->contest->id)
                ->where('entry_id', $entry->id)->count())->toBe(1);
        });
    });

    describe('getAnsweredQuizEntryIds', function () {
        it('returns empty collection when user has no answers', function () {
            $result = $this->service->getAnsweredQuizEntryIds($this->user, $this->contest->id);

            expect($result)->toHaveCount(0);
        });

        it('returns answered quiz entry IDs for the contest', function () {
            $quizEntry1 = QuizEntry::factory()->create(['contest_id' => $this->contest->id]);
            $quizEntry2 = QuizEntry::factory()->create(['contest_id' => $this->contest->id]);
            $otherContestQuiz = QuizEntry::factory()->create();

            $this->user->quizAnswers()->create([
                'quiz_entry_id' => $quizEntry1->id,
                'selected_options' => ['opt_1'],
                'is_correct' => true,
            ]);
            $this->user->quizAnswers()->create([
                'quiz_entry_id' => $quizEntry2->id,
                'selected_options' => ['opt_2'],
                'is_correct' => false,
            ]);
            $this->user->quizAnswers()->create([
                'quiz_entry_id' => $otherContestQuiz->id,
                'selected_options' => ['opt_1'],
                'is_correct' => true,
            ]);

            $result = $this->service->getAnsweredQuizEntryIds($this->user, $this->contest->id);

            expect($result)->toHaveCount(2)
                ->and($result->contains($quizEntry1->id))->toBeTrue()
                ->and($result->contains($quizEntry2->id))->toBeTrue();
        });
    });

    describe('getUserAnswerForQuizEntry', function () {
        it('returns null when user has no answer', function () {
            $entry = QuizEntry::factory()->create(['contest_id' => $this->contest->id]);

            $result = $this->service->getUserAnswerForQuizEntry($this->user, $entry);

            expect($result)->toBeNull();
        });

        it('returns the user answer for the entry', function () {
            $entry = QuizEntry::factory()->create(['contest_id' => $this->contest->id]);
            $answer = $this->user->quizAnswers()->create([
                'quiz_entry_id' => $entry->id,
                'selected_options' => ['opt_1', 'opt_2'],
                'is_correct' => true,
            ]);

            $result = $this->service->getUserAnswerForQuizEntry($this->user, $entry);

            expect($result)->toBeInstanceOf(QuizAnswer::class)
                ->and($result->id)->toBe($answer->id)
                ->and($result->selected_options)->toBe(['opt_1', 'opt_2']);
        });
    });

    describe('hasAnsweredQuizEntry', function () {
        it('returns false when user has no answer', function () {
            $entry = QuizEntry::factory()->create(['contest_id' => $this->contest->id]);

            expect($this->service->hasAnsweredQuizEntry($this->user, $entry))->toBeFalse();
        });

        it('returns true when user has answered', function () {
            $entry = QuizEntry::factory()->create(['contest_id' => $this->contest->id]);
            $this->user->quizAnswers()->create([
                'quiz_entry_id' => $entry->id,
                'selected_options' => ['opt_1'],
                'is_correct' => false,
            ]);

            expect($this->service->hasAnsweredQuizEntry($this->user, $entry))->toBeTrue();
        });

        it('returns false for different entry', function () {
            $entry1 = QuizEntry::factory()->create(['contest_id' => $this->contest->id]);
            $entry2 = QuizEntry::factory()->create(['contest_id' => $this->contest->id]);

            $this->user->quizAnswers()->create([
                'quiz_entry_id' => $entry1->id,
                'selected_options' => ['opt_1'],
                'is_correct' => true,
            ]);

            expect($this->service->hasAnsweredQuizEntry($this->user, $entry2))->toBeFalse();
        });
    });
});
