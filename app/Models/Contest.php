<?php

namespace App\Models;

use App\Enums\ContestType;
use App\Observers\ContestObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property string $status
 * @property string|null $description
 * @property \Illuminate\Carbon|null $start_at
 * @property \Illuminate\Carbon|null $end_at
 * @property mixed $project_schema
 * @property \Illuminate\Database\Eloquent\Collection<int, Project> $projects
 * @property \Illuminate\Database\Eloquent\Collection<int, ContestEntry> $entries
 * @property \Illuminate\Database\Eloquent\Collection<int, QuizEntry> $quizEntries
 * @property User|null $author
 */
#[Fillable(['title', 'type', 'project_schema', 'status', 'description', 'start_at', 'end_at'])]
#[ObservedBy(ContestObserver::class)]
class Contest extends Model
{
    protected $casts = [
        'project_schema' => 'array',
        'type' => ContestType::class,
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * @return HasMany<QuizEntry, $this>
     */
    public function quizEntries(): HasMany
    {
        return $this->hasMany(QuizEntry::class);
    }

    /**
     * @return HasMany<ContestEntry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(ContestEntry::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if the contest is currently active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        $now = now();

        if ($this->start_at && $now->lt($this->start_at)) {
            return false;
        }

        if ($this->end_at && $now->gt($this->end_at)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the contest is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the contest is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if the given user can edit this contest.
     */
    public function canEdit($user): bool
    {
        return $user->exists && $user->id === $this->user_id;
    }
}
