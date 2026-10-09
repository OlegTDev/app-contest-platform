<?php

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\ContestVote;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->contest = Contest::factory()->create();
    $this->entry = ContestEntry::factory()->create(['contest_id' => $this->contest->id]);
});

describe('ContestVote model', function () {
    it('can create a vote with all attributes', function () {
        $vote = ContestVote::factory()->create([
            'contest_id' => $this->contest->id,
            'entry_id' => $this->entry->id,
            'user_id' => $this->user->id,
        ]);

        expect($vote->contest_id)->toBe($this->contest->id)
            ->and($vote->entry_id)->toBe($this->entry->id)
            ->and($vote->user_id)->toBe($this->user->id);
    });

    it('has correct fillable attributes', function () {
        $vote = new ContestVote;

        expect($vote->getFillable())
            ->toBe(['contest_id', 'entry_id', 'user_id']);
    });

    it('uses contest_votes table', function () {
        $vote = new ContestVote;
        expect($vote->getTable())->toBe('contest_votes');
    });

    describe('relationships', function () {
        it('belongs to a contest', function () {
            $vote = ContestVote::factory()->create([
                'contest_id' => $this->contest->id,
                'entry_id' => $this->entry->id,
                'user_id' => $this->user->id,
            ]);

            expect($vote->fresh('contest')->contest->id)->toBe($this->contest->id);
        });

        it('belongs to an entry', function () {
            $vote = ContestVote::factory()->create([
                'contest_id' => $this->contest->id,
                'entry_id' => $this->entry->id,
                'user_id' => $this->user->id,
            ]);

            expect($vote->fresh('entry')->entry->id)->toBe($this->entry->id);
        });

        it('belongs to a voter user', function () {
            $vote = ContestVote::factory()->create([
                'contest_id' => $this->contest->id,
                'entry_id' => $this->entry->id,
                'user_id' => $this->user->id,
            ]);

            expect($vote->fresh('voter')->voter->id)->toBe($this->user->id);
        });
    });

    describe('edge cases', function () {
        it('handles zero vote counts', function () {
            $vote = ContestVote::factory()->create([
                'contest_id' => $this->contest->id,
                'entry_id' => $this->entry->id,
                'user_id' => $this->user->id,
            ]);

            expect($vote->id)->toBeGreaterThan(0);
        });
    });
});
