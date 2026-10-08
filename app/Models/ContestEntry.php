<?php

namespace App\Models;

use Database\Factories\ContestEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $contest_id
 * @property string $title
 * @property string|null $description
 * @property string|null $author_name
 * @property string|null $author_department
 * @property array|null $fields_data
 * @property int $votes_count
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
#[Fillable(['contest_id', 'user_id', 'title', 'description', 'author_name', 'author_department', 'fields_data', 'votes_count'])]
class ContestEntry extends Model
{
    /** @use HasFactory<ContestEntryFactory> */
    use HasFactory;

    protected $casts = [
        'fields_data' => 'array',
        'votes_count' => 'integer',
    ];

    /**
     * @return BelongsTo<Contest, $this>
     */
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ContestVote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(ContestVote::class, 'entry_id');
    }

    /**
     * @return MorphMany<Media, $this>
     */
    public function media(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Media::class, 'entry');
    }

    /**
     * Check if the entry is currently visible (no time restrictions).
     */
    public function isVisible(): bool
    {
        return true;
    }
}
