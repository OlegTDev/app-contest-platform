<?php

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\ContestVote;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'user']);
});

describe('VoteController', function () {
    describe('store', function () {
        it('allows voting for active contest entry', function () {
            $contest = Contest::factory()->create([
                'status' => 'published',
                'start_at' => now()->subDay(),
                'end_at' => now()->addDay(),
            ]);
            $entry = ContestEntry::factory()->create([
                'contest_id' => $contest->id,
                'user_id' => $this->user->id,
            ]);

            $response = $this->actingAs($this->user)->post(
                "/admin/contests/{$contest->id}/entries/{$entry->id}/vote"
            );

            $response->assertRedirect();
            expect(ContestVote::where('contest_id', $contest->id)
                ->where('entry_id', $entry->id)
                ->where('user_id', $this->user->id)->count())->toBe(1);
        });

        it('rejects voting for inactive contest', function () {
            $contest = Contest::factory()->create([
                'status' => 'published',
                'start_at' => now()->addDay(),
                'end_at' => now()->addDays(2),
            ]);
            $entry = ContestEntry::factory()->create([
                'contest_id' => $contest->id,
                'user_id' => $this->user->id,
            ]);

            $response = $this->actingAs($this->user)->post(
                "/admin/contests/{$contest->id}/entries/{$entry->id}/vote"
            );

            $response->assertStatus(403);
        });

        it('rejects voting for entry not belonging to contest', function () {
            $contest = Contest::factory()->create([
                'status' => 'published',
                'start_at' => now()->subDay(),
                'end_at' => now()->addDay(),
            ]);
            $otherContest = Contest::factory()->create();
            $entry = ContestEntry::factory()->create([
                'contest_id' => $otherContest->id,
                'user_id' => $this->user->id,
            ]);

            $response = $this->actingAs($this->user)->post(
                "/admin/contests/{$contest->id}/entries/{$entry->id}/vote"
            );

            $response->assertStatus(404);
        });

        it('prevents duplicate votes', function () {
            $contest = Contest::factory()->create([
                'status' => 'published',
                'start_at' => now()->subDay(),
                'end_at' => now()->addDay(),
            ]);
            $entry = ContestEntry::factory()->create([
                'contest_id' => $contest->id,
                'user_id' => $this->user->id,
            ]);

            ContestVote::create([
                'contest_id' => $contest->id,
                'entry_id' => $entry->id,
                'user_id' => $this->user->id,
            ]);

            $response = $this->actingAs($this->user)->post(
                "/admin/contests/{$contest->id}/entries/{$entry->id}/vote"
            );

            $response->assertRedirect();
            expect(ContestVote::where('contest_id', $contest->id)
                ->where('entry_id', $entry->id)
                ->where('user_id', $this->user->id)->count())->toBe(1);
        });
    });

    describe('destroy', function () {
        it('removes vote', function () {
            $contest = Contest::factory()->create([
                'status' => 'published',
                'start_at' => now()->subDay(),
                'end_at' => now()->addDay(),
            ]);
            $entry = ContestEntry::factory()->create([
                'contest_id' => $contest->id,
                'user_id' => $this->user->id,
            ]);

            $vote = ContestVote::create([
                'contest_id' => $contest->id,
                'entry_id' => $entry->id,
                'user_id' => $this->user->id,
            ]);

            $response = $this->actingAs($this->user)->delete(
                "/admin/contests/{$contest->id}/entries/{$entry->id}/vote"
            );

            $response->assertRedirect();
            expect(ContestVote::where('id', $vote->id)->exists())->toBeFalse();
        });

        it('rejects removing non-existent vote', function () {
            $contest = Contest::factory()->create([
                'status' => 'published',
                'start_at' => now()->subDay(),
                'end_at' => now()->addDay(),
            ]);
            $entry = ContestEntry::factory()->create([
                'contest_id' => $contest->id,
                'user_id' => $this->user->id,
            ]);

            $response = $this->actingAs($this->user)->delete(
                "/admin/contests/{$contest->id}/entries/{$entry->id}/vote"
            );

            $response->assertRedirect();
        });
    });

    describe('leaderboard', function () {
        it('returns leaderboard for active contest', function () {
            $contest = Contest::factory()->create([
                'status' => 'published',
                'start_at' => now()->subDay(),
                'end_at' => now()->addDay(),
            ]);
            $entry1 = ContestEntry::factory()->create([
                'contest_id' => $contest->id,
                'user_id' => $this->user->id,
                'votes_count' => 10,
            ]);
            $entry2 = ContestEntry::factory()->create([
                'contest_id' => $contest->id,
                'user_id' => $this->user->id,
                'votes_count' => 5,
            ]);

            $response = $this->actingAs($this->user)->get(
                "/admin/contests/{$contest->id}/entries/leaderboard"
            );

            $response->assertStatus(200)
                ->assertJsonStructure(['leaderboard']);

            $json = $response->json('leaderboard');
            expect($json[0]['id'])->toBe($entry1->id)
                ->and($json[0]['votes_count'])->toBe(10)
                ->and($json[1]['id'])->toBe($entry2->id);
        });

        it('rejects leaderboard for inactive contest', function () {
            $contest = Contest::factory()->create([
                'status' => 'published',
                'start_at' => now()->addDay(),
                'end_at' => now()->addDays(2),
            ]);

            $response = $this->actingAs($this->user)->get(
                "/admin/contests/{$contest->id}/entries/leaderboard"
            );

            $response->assertStatus(403);
        });
    });
});
