<?php

namespace App\Models;

use Database\Factories\EntryViewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $contest_id
 * @property int $entry_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'contest_id', 'entry_id'])]
class EntryView extends Model
{
    /** @use HasFactory<EntryViewFactory> */
    use HasFactory;

    protected $casts = [];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
        return $this->belongsTo(ContestEntry::class);
    }
}
