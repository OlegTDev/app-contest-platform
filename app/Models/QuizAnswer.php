<?php

namespace App\Models;

use Database\Factories\QuizAnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $quiz_entry_id
 * @property int $user_id
 * @property array<string, mixed>|null $selected_options
 * @property bool $is_correct
 * @property Carbon|null $answered_at
 */
#[Fillable(['quiz_entry_id', 'user_id', 'selected_options', 'is_correct', 'answered_at'])]
class QuizAnswer extends Model
{
    /** @use HasFactory<QuizAnswerFactory> */
    use HasFactory;

    protected $casts = [
        'selected_options' => 'array',
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<QuizEntry, $this>
     */
    public function quizEntry(): BelongsTo
    {
        return $this->belongsTo(QuizEntry::class, 'quiz_entry_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
