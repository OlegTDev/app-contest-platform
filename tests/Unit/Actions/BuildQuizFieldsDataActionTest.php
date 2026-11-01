<?php

use App\Actions\BuildQuizFieldsDataAction;

beforeEach(function () {
    $this->action = new BuildQuizFieldsDataAction;
});

describe('BuildQuizFieldsDataAction', function () {
    describe('basic functionality', function () {
        it('builds fields data with options', function () {
            $options = [
                ['text' => 'Option A', 'is_correct' => true],
                ['text' => 'Option B', 'is_correct' => false],
                ['text' => 'Option C', 'is_correct' => false],
            ];
            $question = 'What is 2+2?';
            $type = 'single';

            $result = $this->action->execute($options, $question, $type);

            expect($result)->not->toBeNull()
                ->and($result['type'])->toBe('single')
                ->and($result['question'])->toBe('What is 2+2?')
                ->and($result['options'])->toHaveCount(3);
        });

        it('assigns sequential IDs to options', function () {
            $options = [
                ['text' => 'First', 'is_correct' => false],
                ['text' => 'Second', 'is_correct' => true],
            ];

            $result = $this->action->execute($options, 'Question?', 'single');

            expect($result['options'][0]['id'])->toBe('opt_1')
                ->and($result['options'][1]['id'])->toBe('opt_2');
        });

        it('preserves option text and correctness', function () {
            $options = [
                ['text' => 'Correct Answer', 'is_correct' => true],
                ['text' => 'Wrong Answer', 'is_correct' => false],
            ];

            $result = $this->action->execute($options, '', 'multiple');

            expect($result['options'][0]['text'])->toBe('Correct Answer')
                ->and($result['options'][0]['is_correct'])->toBeTrue()
                ->and($result['options'][1]['text'])->toBe('Wrong Answer')
                ->and($result['options'][1]['is_correct'])->toBeFalse();
        });

        it('handles multiple correct options', function () {
            $options = [
                ['text' => 'A', 'is_correct' => true],
                ['text' => 'B', 'is_correct' => true],
                ['text' => 'C', 'is_correct' => false],
            ];

            $result = $this->action->execute($options, '', 'multiple');

            expect($result['options'][0]['is_correct'])->toBeTrue()
                ->and($result['options'][1]['is_correct'])->toBeTrue()
                ->and($result['options'][2]['is_correct'])->toBeFalse();
        });
    });

    describe('question handling', function () {
        it('includes question when not empty', function () {
            $options = [['text' => 'A', 'is_correct' => true]];

            $result = $this->action->execute($options, 'Test question', 'single');

            expect($result['question'])->toBe('Test question');
        });

        it('excludes question when empty', function () {
            $options = [['text' => 'A', 'is_correct' => true]];

            $result = $this->action->execute($options, '', 'single');

            expect($result['type'])->toBe('single')
                ->and($result['options'])->toHaveCount(1)
                ->and($result)->not->toHaveKey('question');
        });

        it('handles whitespace-only question as non-empty', function () {
            $options = [['text' => 'A', 'is_correct' => true]];

            $result = $this->action->execute($options, '   ', 'single');

            // Whitespace is not empty string, so it should be included
            expect($result['question'])->toBe('   ');
        });
    });

    describe('type handling', function () {
        it('preserves single type', function () {
            $options = [['text' => 'A', 'is_correct' => true]];

            $result = $this->action->execute($options, '', 'single');

            expect($result['type'])->toBe('single');
        });

        it('preserves multiple type', function () {
            $options = [['text' => 'A', 'is_correct' => true]];

            $result = $this->action->execute($options, '', 'multiple');

            expect($result['type'])->toBe('multiple');
        });
    });

    describe('edge cases', function () {
        it('handles single option', function () {
            $options = [['text' => 'Only Option', 'is_correct' => true]];

            $result = $this->action->execute($options, '', 'single');

            expect($result['options'])->toHaveCount(1)
                ->and($result['options'][0]['id'])->toBe('opt_1');
        });

        it('handles many options', function () {
            $options = [];
            for ($i = 1; $i <= 10; $i++) {
                $options[] = ['text' => "Option {$i}", 'is_correct' => ($i === 1)];
            }

            $result = $this->action->execute($options, '', 'multiple');

            expect($result['options'])->toHaveCount(10)
                ->and($result['options'][0]['id'])->toBe('opt_1')
                ->and($result['options'][9]['id'])->toBe('opt_10');
        });

        it('handles options with special characters in text', function () {
            $options = [
                ['text' => 'Café résumé', 'is_correct' => true],
                ['text' => '日本語テスト', 'is_correct' => false],
            ];

            $result = $this->action->execute($options, 'Question?', 'single');

            expect($result['options'][0]['text'])->toBe('Café résumé')
                ->and($result['options'][1]['text'])->toBe('日本語テスト');
        });
    });
});
