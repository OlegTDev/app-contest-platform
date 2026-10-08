<?php

namespace App\Models;

use Database\Factories\QuizEntryFactory;
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
 * @property array|null $fields_data
 * @property int $sort_order
 * @property \Illuminate\Carbon|null $show_from
 * @property \Illuminate\Carbon|null $show_until
 * @property \Illuminate\Database\Eloquent\Collection<int, QuizAnswer> $answers
 */
#[Fillable(['contest_id', 'title', 'description', 'fields_data', 'sort_order', 'show_from', 'show_until'])]
class QuizEntry extends Model
{
    /** @use HasFactory<QuizEntryFactory> */
    use HasFactory;

    protected $casts = [
        'fields_data' => 'array',
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
     * @return HasMany<QuizAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    /**
     * @return MorphMany<Media, $this>
     */
    public function media(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Media::class, 'entry');
    }

    /**
     * Check if the entry is currently visible.
     */
    public function isVisible(): bool
    {
        if (!$this->show_from || !$this->show_until) {
            return true;
        }

        $now = now();

        if ($now->lt($this->show_from)) {
            return false;
        }

        if ($now->gt($this->show_until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the entry is scheduled for future display.
     */
    public function isScheduled(): bool
    {
        return $this->show_from !== null && $this->show_until !== null;
    }
}
