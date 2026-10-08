<?php

namespace App\Models;

use Database\Factories\ContestVoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $contest_id
 * @property int $entry_id
 * @property int $user_id
 */
#[Fillable(['contest_id', 'entry_id', 'user_id'])]
class ContestVote extends Model
{
    /** @use HasFactory<ContestVoteFactory> */
    use HasFactory;

    protected $table = 'contest_votes';

    /**
     * @return BelongsTo<Contest, $this>
     */
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * @return BelongsTo<ContestEntry, $this>
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(ContestEntry::class, 'entry_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
