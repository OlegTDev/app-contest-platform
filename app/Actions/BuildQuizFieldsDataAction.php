<?php

declare(strict_types=1);

namespace App\Actions;

/**
 * Builds fields_data array from quiz card fields.
 *
 * Used by both StoreQuizEntry and UpdateQuizEntry flows.
 */
class BuildQuizFieldsDataAction
{
    /**
     * @param  array<int, array{text: string, is_correct: bool}>  $options
     * @return array{question?: string, options: array<int, array{id: string, text: string, is_correct: bool}>, type: string}|null
     */
    public function execute(array $options, string $question, string $type): ?array
    {
        $optionsWithIds = array_map(function ($opt, $index) {
            return [
                'id' => 'opt_'.($index + 1),
                'text' => $opt['text'],
                'is_correct' => $opt['is_correct'],
            ];
        }, $options, range(0, count($options) - 1));

        $data = [
            'options' => $optionsWithIds,
            'type' => $type,
        ];

        if ($question !== '') {
            $data['question'] = $question;
        }

        return $data;
    }
}
