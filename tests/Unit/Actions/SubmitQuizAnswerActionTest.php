<?php

use App\Actions\CheckQuizAnswerAction;
use App\Actions\SubmitQuizAnswerAction;
use App\Models\QuizAnswer;
use App\Models\QuizEntry;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->contest = \App\Models\Contest::factory()->create();
    $this->entry = QuizEntry::factory()->create([
        'contest_id' => $this->contest->id,
        'fields_data' => [
            'question' => 'What is 2+2?',
            'options' => [
                ['id' => 'opt_1', 'text' => '3', 'is_correct' => false],
                ['id' => 'opt_2', 'text' => '4', 'is_correct' => true],
                ['id' => 'opt_3', 'text' => '5', 'is_correct' => false],
            ],
            'type' => 'single',
        ],
    ]);
    $this->action = new SubmitQuizAnswerAction(new CheckQuizAnswerAction);
});

describe('SubmitQuizAnswerAction', function () {
    describe('correct answer submission', function () {
        it('creates a new answer when none exists', function () {
            $answer = $this->action->execute($this->entry, $this->user->id, ['opt_2']);

            expect($answer)->toBeInstanceOf(QuizAnswer::class)
                ->and($answer->user_id)->toBe($this->user->id)
                ->and($answer->quiz_entry_id)->toBe($this->entry->id)
                ->and($answer->is_correct)->toBeTrue()
                ->and($answer->selected_options)->toBe(['opt_2']);
        });

        it('marks answer as correct when options match', function () {
            $answer = $this->action->execute($this->entry, $this->user->id, ['opt_2']);

            expect($answer->is_correct)->toBeTrue();
        });

        it('marks answer as incorrect when options do not match', function () {
            $answer = $this->action->execute($this->entry, $this->user->id, ['opt_1']);

            expect($answer->is_correct)->toBeFalse();
        });

        it('sets answered_at timestamp', function () {
            $answer = $this->action->execute($this->entry, $this->user->id, ['opt_2']);

            expect($answer->answered_at)->not->toBeNull()
                ->and($answer->answered_at)->toBeInstanceOf(CarbonImmutable::class);
        });
    });

    describe('existing answer handling', function () {
        it('returns existing answer without creating a new one', function () {
            $firstAnswer = $this->action->execute($this->entry, $this->user->id, ['opt_2']);

            // Try to submit again with different options
            $secondAnswer = $this->action->execute($this->entry, $this->user->id, ['opt_1']);

            expect($secondAnswer->id)->toBe($firstAnswer->id)
                ->and(QuizAnswer::where('user_id', $this->user->id)
                    ->where('quiz_entry_id', $this->entry->id)
                    ->count())->toBe(1);
        });

        it('preserves the original answer data', function () {
            $firstAnswer = $this->action->execute($this->entry, $this->user->id, ['opt_2']);

            $secondAnswer = $this->action->execute($this->entry, $this->user->id, ['opt_1']);

            expect($secondAnswer->selected_options)->toBe(['opt_2'])
                ->and($secondAnswer->is_correct)->toBeTrue();
        });
    });

    describe('multiple users, same entry', function () {
        it('allows different answers for different users', function () {
            $otherUser = User::factory()->create();

            $answer1 = $this->action->execute($this->entry, $this->user->id, ['opt_2']);
            $answer2 = $this->action->execute($this->entry, $otherUser->id, ['opt_1']);

            expect($answer1->is_correct)->toBeTrue()
                ->and($answer2->is_correct)->toBeFalse()
                ->and(QuizAnswer::where('quiz_entry_id', $this->entry->id)->count())->toBe(2);
        });
    });

    describe('multiple correct options (multi-answer quiz)', function () {
        beforeEach(function () {
            $this->multiEntry = QuizEntry::factory()->create([
                'contest_id' => $this->contest->id,
                'fields_data' => [
                    'question' => 'Select all correct',
                    'options' => [
                        ['id' => 'opt_1', 'text' => 'A', 'is_correct' => true],
                        ['id' => 'opt_2', 'text' => 'B', 'is_correct' => true],
                        ['id' => 'opt_3', 'text' => 'C', 'is_correct' => false],
                    ],
                    'type' => 'multiple',
                ],
            ]);
        });

        it('marks fully correct multi-answer as correct', function () {
            $answer = $this->action->execute($this->multiEntry, $this->user->id, ['opt_1', 'opt_2']);

            expect($answer->is_correct)->toBeTrue();
        });

        it('marks partially correct multi-answer as incorrect', function () {
            $answer = $this->action->execute($this->multiEntry, $this->user->id, ['opt_1']);

            expect($answer->is_correct)->toBeFalse();
        });

        it('marks answer with wrong option as incorrect', function () {
            $answer = $this->action->execute($this->multiEntry, $this->user->id, ['opt_1', 'opt_3']);

            expect($answer->is_correct)->toBeFalse();
        });
    });

    describe('transaction safety', function () {
        it('does not create duplicate answers under concurrent-like conditions', function () {
            // Simulate the race condition protection by manually creating an answer
            // then trying to submit
            QuizAnswer::create([
                'quiz_entry_id' => $this->entry->id,
                'user_id' => $this->user->id,
                'selected_options' => ['opt_1'],
                'is_correct' => false,
                'answered_at' => now(),
            ]);

            $answer = $this->action->execute($this->entry, $this->user->id, ['opt_2']);

            expect($answer->selected_options)->toBe(['opt_1'])
                ->and(QuizAnswer::where('user_id', $this->user->id)
                    ->where('quiz_entry_id', $this->entry->id)
                    ->count())->toBe(1);
        });
    });
});
