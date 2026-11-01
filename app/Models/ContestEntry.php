<?php

namespace App\Models;

use App\Observers\ContestEntryObserver;
use App\Services\TimeWindowService;
use Database\Factories\ContestEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 * @property int $contest_id
 * @property string $title
 * @property string|null $description
 * @property string|null $author_name
 * @property string|null $author_department
 * @property array<string, mixed>|null $fields_data
 * @property int $votes_count
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['contest_id', 'user_id', 'title', 'description', 'author_name', 'author_department', 'fields_data', 'votes_count'])]
#[ObservedBy(ContestEntryObserver::class)]
class ContestEntry extends Model
{
    /** @use HasFactory<ContestEntryFactory> */
    use HasFactory;

    protected $casts = [
        'fields_data' => 'array',
        'votes_count' => 'integer',
        'show_from' => 'datetime',
        'show_until' => 'datetime',
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
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'entry');
    }

    /**
     * Check if the entry is currently visible based on time window.
     */
    public function isVisible(): bool
    {
        return app(TimeWindowService::class)->isWithinWindow($this->show_from, $this->show_until);
    }

    /**
     * Get author name — uses user relation if loaded, falls back to denormalized field.
     */
    public function getAuthorNameAttribute(): ?string
    {
        if ($this->relationLoaded('user') && $this->user !== null) {
            return $this->user->name;
        }

        return $this->attributes['author_name'] ?? null;
    }

    /**
     * Get author department — uses user relation if loaded, falls back to denormalized field.
     */
    public function getAuthorDepartmentAttribute(): ?string
    {
        if ($this->relationLoaded('user') && $this->user !== null) {
            return $this->user->department;
        }

        return $this->attributes['author_department'] ?? null;
    }
}
