<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\QuizEntry;

/**
 * Checks if a quiz answer is correct.
 */
class CheckQuizAnswerAction
{
    /**
     * @param  array<int, string>  $selectedOptions
     */
    public function execute(QuizEntry $entry, array $selectedOptions): bool
    {
        $options = $entry->options;
        if ($options === null || empty($options)) {
            return false;
        }

        $correctOptionIds = array_filter($options, fn ($opt) => $opt['is_correct'] === true);
        $correctIds = array_map(fn ($opt) => $opt['id'], $correctOptionIds);
        $expectedCount = count($correctIds);
        $selectedCount = count($selectedOptions);

        // For single-answer quizzes, counts must match
        if ($entry->type === 'single' && $expectedCount !== $selectedCount) {
            return false;
        }

        // All selected must be correct and all correct must be selected
        return array_diff($selectedOptions, $correctIds) === []
            && array_diff($correctIds, $selectedOptions) === [];
    }
}
