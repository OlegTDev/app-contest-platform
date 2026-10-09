<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\ContestVote;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    /**
     * Vote for an entry.
     */
    public function store(Request $request, Contest $contest, ContestEntry $entry): RedirectResponse
    {
        abort_unless($contest->isActive(), 403, 'This contest is not currently available.');
        abort_unless($entry->contest_id === $contest->id, 404);
        abort_unless($entry->isVisible(), 403, 'This entry is not currently available.');

        $user = $request->user();

        if (! $user instanceof User) {
            return back()->with('error', 'Необходима авторизация.');
        }

        // Check if user already voted for this entry
        $existing = ContestVote::where('contest_id', $contest->id)
            ->where('entry_id', $entry->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Вы уже проголосовали за эту работу.');
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

        return back()->with('success', 'Голос засчитан!');
    }

    /**
     * Remove a vote.
     */
    public function destroy(Request $request, Contest $contest, ContestEntry $entry): RedirectResponse
    {
        abort_unless($contest->isActive(), 403, 'This contest is not currently available.');
        abort_unless($entry->contest_id === $contest->id, 404);

        $user = $request->user();

        if (! $user instanceof User) {
            return back()->with('error', 'Необходима авторизация.');
        }

        $vote = ContestVote::where('contest_id', $contest->id)
            ->where('entry_id', $entry->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $vote) {
            return back()->with('error', 'Вы не голосовали за эту работу.');
        }

        DB::transaction(function () use ($vote, $entry) {
            $vote->delete();
            $entry->decrement('votes_count');
        });

        return back()->with('success', 'Голос удалён.');
    }

    /**
     * Get leaderboard for a contest.
     */
    public function leaderboard(Request $request, Contest $contest): JsonResponse
    {
        abort_unless($contest->isActive(), 403, 'This contest is not currently available.');

        $leaderboard = $contest->entries()
            ->orderByDesc('votes_count')
            ->limit(50)
            ->get()
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'title' => $entry->title,
                'votes_count' => $entry->votes_count,
                'author_name' => $entry->author_name,
            ]);

        return response()->json(['leaderboard' => $leaderboard]);
    }
}
