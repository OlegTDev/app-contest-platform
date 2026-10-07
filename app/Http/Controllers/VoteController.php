<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\ContestVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    /**
     * Vote for an entry.
     */
    public function store(Request $request, Contest $contest, ContestEntry $entry): JsonResponse
    {
        abort_unless($contest->isActive(), 403, 'This contest is not currently available.');
        abort_unless($entry->contest_id === $contest->id, 404);
        abort_unless($entry->isVisible(), 403, 'This entry is not currently available.');

        $user = $request->user();

        // Check if user already voted for this entry
        $existing = ContestVote::where('contest_id', $contest->id)
            ->where('entry_id', $entry->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You have already voted for this entry.',
                'already_voted' => true,
            ], 422);
        }

        DB::transaction(function () use ($contest, $entry, $user) {
            // Create vote
            ContestVote::create([
                'contest_id' => $contest->id,
                'entry_id' => $entry->id,
                'user_id' => $user->id,
            ]);

            // Increment votes count
            $entry->increment('votes_count');
        });

        return response()->json([
            'message' => 'Vote recorded successfully.',
            'votes_count' => $entry->votes_count + 1,
            'already_voted' => false,
        ]);
    }

    /**
     * Remove a vote.
     */
    public function destroy(Request $request, Contest $contest, ContestEntry $entry): JsonResponse
    {
        abort_unless($contest->isActive(), 403, 'This contest is not currently available.');
        abort_unless($entry->contest_id === $contest->id, 404);

        $user = $request->user();

        $vote = ContestVote::where('contest_id', $contest->id)
            ->where('entry_id', $entry->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$vote) {
            return response()->json([
                'message' => 'You have not voted for this entry.',
            ], 422);
        }

        DB::transaction(function () use ($vote, $entry) {
            $vote->delete();
            $entry->decrement('votes_count');
        });

        return response()->json([
            'message' => 'Vote removed successfully.',
            'votes_count' => max(0, $entry->votes_count - 1),
        ]);
    }

    /**
     * Get leaderboard for a contest.
     */
    public function leaderboard(Request $request, Contest $contest): JsonResponse
    {
        abort_unless($contest->isActive(), 403, 'This contest is not currently available.');

        $leaderboard = $contest->entries()
            ->with(['author'])
            ->whereNotNull('votes_count')
            ->orderByDesc('votes_count')
            ->limit(50)
            ->get()
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'title' => $entry->title,
                'votes_count' => $entry->votes_count,
                'author' => $entry->author ? [
                    'name' => $entry->author->name,
                ] : null,
            ]);

        return response()->json(['leaderboard' => $leaderboard]);
    }
}
