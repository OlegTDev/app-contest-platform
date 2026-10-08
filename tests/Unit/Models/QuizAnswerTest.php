<?php

use App\Models\QuizAnswer;
use App\Models\QuizEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->entry = QuizEntry::factory()->create();
});

describe('QuizAnswer model', function () {
    it('can create an answer with all attributes', function () {
        $answer = QuizAnswer::factory()->create([
            'quiz_entry_id' => $this->entry->id,
            'user_id' => $this->user->id,
            'answer' => 'Paris',
            'is_correct' => true,
            'answered_at' => now(),
        ]);

        expect($answer->quiz_entry_id)->toBe($this->entry->id)
            ->and($answer->user_id)->toBe($this->user->id)
            ->and($answer->answer)->toBe('Paris')
            ->and($answer->is_correct)->toBeTrue()
            ->and($answer->answered_at)->not->toBeNull();
    });

    it('uses the quiz_answers table', function () {
        $answer = new QuizAnswer();

        expect($answer->getTable())->toBe('quiz_answers');
    });

    it('has correct fillable attributes', function () {
        $answer = new QuizAnswer();

        expect($answer->getFillable())
            ->toBe(['quiz_entry_id', 'user_id', 'answer', 'is_correct', 'answered_at']);
    });

    it('casts is_correct as a boolean', function () {
        $answer = QuizAnswer::factory()->create(['is_correct' => 1]);

        expect($answer->is_correct)->toBeBool()->toBeTrue();

        $answer->update(['is_correct' => 0]);

        expect($answer->fresh()->is_correct)->toBeFalse();
    });

    it('casts answered_at as a datetime', function () {
        $answeredAt = now();

        $answer = QuizAnswer::factory()->create(['answered_at' => $answeredAt]);

        expect($answer->answered_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($answer->answered_at->timestamp)->toBe($answeredAt->timestamp);
    });

    describe('relationships', function () {
        it('belongs to a quiz entry', function () {
            $answer = QuizAnswer::factory()->create(['quiz_entry_id' => $this->entry->id]);

            expect($answer->fresh('quizEntry')->quizEntry->id)->toBe($this->entry->id);
        });

        it('belongs to a user', function () {
            $answer = QuizAnswer::factory()->create(['user_id' => $this->user->id]);

            expect($answer->fresh('user')->user->id)->toBe($this->user->id);
        });

        it('is exposed through the quiz entry answers collection', function () {
            $answer = QuizAnswer::factory()->create(['quiz_entry_id' => $this->entry->id]);

            expect($this->entry->answers()->whereKey($answer->id)->exists())->toBeTrue()
                ->and($this->entry->fresh('answers')->answers->pluck('id')->contains($answer->id))->toBeTrue();
        });
    });

    describe('unique constraint', function () {
        it('allows one answer per user per quiz entry', function () {
            QuizAnswer::factory()->create([
                'quiz_entry_id' => $this->entry->id,
                'user_id' => $this->user->id,
            ]);

            $otherUser = User::factory()->create();

            $second = QuizAnswer::factory()->create([
                'quiz_entry_id' => $this->entry->id,
                'user_id' => $otherUser->id,
            ]);

            expect(QuizAnswer::count())->toBe(2)
                ->and($second->user_id)->toBe($otherUser->id);
        });

        it('rejects a duplicate answer for the same user and quiz entry', function () {
            QuizAnswer::factory()->create([
                'quiz_entry_id' => $this->entry->id,
                'user_id' => $this->user->id,
            ]);

            expect(fn () => QuizAnswer::factory()->create([
                'quiz_entry_id' => $this->entry->id,
                'user_id' => $this->user->id,
            ]))->toThrow(UniqueConstraintViolationException::class);
        });
    });

    describe('edge cases', function () {
        it('handles a null answered_at', function () {
            $answer = QuizAnswer::factory()->create(['answered_at' => null]);

            expect($answer->answered_at)->toBeNull();
        });

        it('defaults is_correct to false', function () {
            $answer = $this->entry->answers()->create([
                'user_id' => $this->user->id,
                'answer' => 'Wrong answer',
            ]);

            expect($answer->fresh()->is_correct)->toBeFalse();
        });
    });
});
