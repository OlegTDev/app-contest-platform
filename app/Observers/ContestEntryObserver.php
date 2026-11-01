<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ContestEntry;
use App\Models\ContestVote;

/**
 * Keeps ContestEntry.votes_count in sync with actual ContestVote records.
 */
final readonly class ContestEntryObserver
{
    /**
     * Handle the ContestVote "created" event.
     */
    public function created(ContestVote $vote): void
    {
        $vote->entry->increment('votes_count');
    }

    /**
     * Handle the ContestVote "deleted" event.
     */
    public function deleted(ContestVote $vote): void
    {
        $vote->entry->decrement('votes_count');
    }

    /**
     * Recalculate votes_count from scratch.
     * Useful for data integrity fixes.
     */
    public static function recalculate(ContestEntry $entry): void
    {
        $entry->updateQuietly([
            'votes_count' => ContestVote::where('entry_id', $entry->id)->count(),
        ]);
    }
}
