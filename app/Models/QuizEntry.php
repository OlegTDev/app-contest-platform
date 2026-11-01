<?php

namespace App\Models;

use App\Services\TimeWindowService;
use Database\Factories\QuizEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
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
 * @property array<string, mixed>|null $fields_data
 * @property int $sort_order
 * @property Carbon|null $show_from
 * @property Carbon|null $show_until
 * @property Collection<int, QuizAnswer> $answers
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
     * Get the question text from fields_data.
     */
    public function getQuestionAttribute(): ?string
    {
        return $this->fields_data['question'] ?? null;
    }

    /**
     * Get the options array from fields_data.
     *
     * @return array<int, array{id: string, text: string, is_correct: bool}>|null
     */
    public function getOptionsAttribute(): ?array
    {
        $options = $this->fields_data['options'] ?? null;
        if (! is_array($options)) {
            return null;
        }

        return array_map(function ($opt) {
            return [
                'id' => $opt['id'] ?? '',
                'text' => $opt['text'] ?? '',
                'is_correct' => (bool) ($opt['is_correct'] ?? false),
            ];
        }, $options);
    }

    /**
     * Get the quiz type from fields_data.
     */
    public function getTypeAttribute(): string
    {
        return $this->fields_data['type'] ?? 'single';
    }

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
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'entry');
    }

    /**
     * Check if the entry is currently visible.
     */
    public function isVisible(): bool
    {
        return app(TimeWindowService::class)->isWithinWindow($this->show_from, $this->show_until);
    }

    /**
     * Check if the entry is scheduled for future display.
     */
    public function isScheduled(): bool
    {
        return $this->show_from !== null && $this->show_until !== null;
    }
}
