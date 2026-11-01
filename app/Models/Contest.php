<?php

namespace App\Models;

use App\Enums\ContestType;
use App\Observers\ContestObserver;
use App\Services\TimeWindowService;
use Carbon\CarbonInterface;
use Database\Factories\ContestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property ContestType $type
 * @property string $title
 * @property string $status
 * @property string|null $slug
 * @property string|null $description
 * @property CarbonInterface|null $start_at
 * @property CarbonInterface|null $end_at
 * @property mixed $project_schema
 * @property Collection<int, Project> $projects
 * @property Collection<int, ContestEntry> $entries
 * @property Collection<int, QuizEntry> $quizEntries
 * @property User|null $author
 */
#[Fillable(['title', 'type', 'project_schema', 'status', 'slug', 'description', 'start_at', 'end_at'])]
#[ObservedBy(ContestObserver::class)]
class Contest extends Model
{
    /** @use HasFactory<ContestFactory> */
    use HasFactory;

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
        return $this->hasMany(ContestEntry::class)->orderBy('id');
    }

    /**
     * @return HasMany<Media, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
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

        return app(TimeWindowService::class)->isContestActive($this);
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
     * Scope: only published contests.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    /**
     * Check if the given user can edit this contest.
     */
    public function canEdit(User $user): bool
    {
        return $user->exists && $user->id === $this->user_id;
    }

    /**
     * Resolve the route binding for a given value.
     * Tries numeric ID first, then falls back to slug.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        if (is_numeric($value)) {
            $contest = $this->find($value);
            if ($contest) {
                return $contest;
            }
        }

        $contest = $this->where('slug', $value)->first();
        if ($contest) {
            return $contest;
        }

        if (! is_numeric($value)) {
            $contest = $this->find($value);
            if ($contest) {
                return $contest;
            }
        }

        return null;
    }
}
