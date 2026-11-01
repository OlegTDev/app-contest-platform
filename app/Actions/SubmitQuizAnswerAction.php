<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\QuizAnswer;
use App\Models\QuizEntry;
use Illuminate\Support\Facades\DB;

/**
 * Submits a quiz answer for a user.
 */
class SubmitQuizAnswerAction
{
    public function __construct(
        private readonly CheckQuizAnswerAction $checkAnswer
    ) {}

    /**
     * @param  array<int, string>  $selectedOptions
     */
    public function execute(QuizEntry $entry, int $userId, array $selectedOptions): QuizAnswer
    {
        $isCorrect = $this->checkAnswer->execute($entry, $selectedOptions);

        return DB::transaction(function () use ($entry, $userId, $selectedOptions, $isCorrect): QuizAnswer {
            return QuizAnswer::create([
                'quiz_entry_id' => $entry->id,
                'user_id' => $userId,
                'selected_options' => $selectedOptions,
                'is_correct' => $isCorrect,
                'answered_at' => now(),
            ]);
        });
    }
}
