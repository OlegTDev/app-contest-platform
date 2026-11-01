<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Handles user activity tracking per contest (votes, views, quiz answers).
 * Extracted from ContestService to follow single responsibility principle.
 */
final readonly class UserContestActivityService
{
    /**
     * Get entry IDs the user has voted for in a contest.
     *
     * @return Collection<int, int>
     */
    public function getUserVoteEntryIds(User $user, int $contestId): Collection
    {
        return $user->votes()
            ->where('contest_id', $contestId)
            ->pluck('entry_id');
    }

    /**
     * Get entry IDs the user has viewed in a contest.
     *
     * @return Collection<int, int>
     */
    public function getViewedEntryIds(User $user, int $contestId): Collection
    {
        return $user->entryViews()
            ->where('contest_id', $contestId)
            ->pluck('entry_id');
    }

    /**
     * Mark an entry as viewed by a user.
     */
    public function markEntryAsViewed(User $user, int $contestId, int $entryId): void
    {
        $user->entryViews()->updateOrCreate(
            ['contest_id' => $contestId, 'entry_id' => $entryId],
            ['contest_id' => $contestId, 'entry_id' => $entryId]
        );
    }

    /**
     * Get quiz entry IDs the user has answered in a contest.
     *
     * @return Collection<int, int>
     */
    public function getAnsweredQuizEntryIds(User $user, int $contestId): Collection
    {
        return $user->quizAnswers()
            ->whereHas('quizEntry', fn ($query) => $query->where('contest_id', $contestId))
            ->pluck('quiz_entry_id');
    }
}
