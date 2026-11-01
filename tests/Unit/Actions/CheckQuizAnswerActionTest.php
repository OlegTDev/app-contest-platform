<?php

use App\Actions\CheckQuizAnswerAction;
use App\Models\QuizEntry;

beforeEach(function () {
    $this->action = new CheckQuizAnswerAction;
});

describe('CheckQuizAnswerAction', function () {
    describe('single-answer quizzes', function () {
        it('returns true when single correct option is selected', function () {
            $entry = new class extends QuizEntry {
                public array $options = [
                    ['id' => 'opt_1', 'text' => 'Wrong', 'is_correct' => false],
                    ['id' => 'opt_2', 'text' => 'Correct', 'is_correct' => true],
                    ['id' => 'opt_3', 'text' => 'Also Wrong', 'is_correct' => false],
                ];

                public string $quizType = 'single';

                public function getOptionsAttribute(): ?array
                {
                    return $this->options;
                }

                public function getTypeAttribute(): string
                {
                    return $this->quizType;
                }
            };

            expect($this->action->execute($entry, ['opt_2']))->toBeTrue();
        });

        it('returns false when wrong option is selected', function () {
            $entry = new class extends QuizEntry {
                public array $options = [
                    ['id' => 'opt_1', 'text' => 'Wrong', 'is_correct' => false],
                    ['id' => 'opt_2', 'text' => 'Correct', 'is_correct' => true],
                ];

                public string $quizType = 'single';

                public function getOptionsAttribute(): ?array
                {
                    return $this->options;
                }

                public function getTypeAttribute(): string
                {
                    return $this->quizType;
                }
            };

            expect($this->action->execute($entry, ['opt_1']))->toBeFalse();
        });

        it('returns false when multiple options are selected for single quiz', function () {
            $entry = new class extends QuizEntry {
                public array $options = [
                    ['id' => 'opt_1', 'text' => 'Correct', 'is_correct' => true],
                    ['id' => 'opt_2', 'text' => 'Wrong', 'is_correct' => false],
                ];

                public string $quizType = 'single';

                public function getOptionsAttribute(): ?array
                {
                    return $this->options;
                }

                public function getTypeAttribute(): string
                {
                    return $this->quizType;
                }
            };

            expect($this->action->execute($entry, ['opt_1', 'opt_2']))->toBeFalse();
        });

        it('returns false when no options are selected', function () {
            $entry = new class extends QuizEntry {
                public array $options = [
                    ['id' => 'opt_1', 'text' => 'Correct', 'is_correct' => true],
                ];

                public string $quizType = 'single';

                public function getOptionsAttribute(): ?array
                {
                    return $this->options;
                }

                public function getTypeAttribute(): string
                {
                    return $this->quizType;
                }
            };

            expect($this->action->execute($entry, []))->toBeFalse();
        });
    });

    describe('multi-answer quizzes', function () {
        it('returns true when all correct options are selected', function () {
            $entry = new class extends QuizEntry {
                public array $options = [
                    ['id' => 'opt_1', 'text' => 'Correct 1', 'is_correct' => true],
                    ['id' => 'opt_2', 'text' => 'Correct 2', 'is_correct' => true],
                    ['id' => 'opt_3', 'text' => 'Wrong', 'is_correct' => false],
                ];

                public string $quizType = 'multiple';

                public function getOptionsAttribute(): ?array
                {
                    return $this->options;
                }

                public function getTypeAttribute(): string
                {
                    return $this->quizType;
                }
            };

            expect($this->action->execute($entry, ['opt_1', 'opt_2']))->toBeTrue();
        });

        it('returns false when only some correct options are selected', function () {
            $entry = new class extends QuizEntry {
                public array $options = [
                    ['id' => 'opt_1', 'text' => 'Correct 1', 'is_correct' => true],
                    ['id' => 'opt_2', 'text' => 'Correct 2', 'is_correct' => true],
                    ['id' => 'opt_3', 'text' => 'Wrong', 'is_correct' => false],
                ];

                public string $quizType = 'multiple';

                public function getOptionsAttribute(): ?array
                {
                    return $this->options;
                }

                public function getTypeAttribute(): string
                {
                    return $this->quizType;
                }
            };

            expect($this->action->execute($entry, ['opt_1']))->toBeFalse();
        });

        it('returns false when a wrong option is included', function () {
            $entry = new class extends QuizEntry {
                public array $options = [
                    ['id' => 'opt_1', 'text' => 'Correct', 'is_correct' => true],
                    ['id' => 'opt_2', 'text' => 'Wrong', 'is_correct' => false],
                ];

                public string $quizType = 'multiple';

                public function getOptionsAttribute(): ?array
                {
                    return $this->options;
                }

                public function getTypeAttribute(): string
                {
                    return $this->quizType;
                }
            };

            expect($this->action->execute($entry, ['opt_1', 'opt_2']))->toBeFalse();
        });

        it('returns true when all options are correct and all are selected', function () {
            $entry = new class extends QuizEntry {
                public array $options = [
                    ['id' => 'opt_1', 'text' => 'A', 'is_correct' => true],
                    ['id' => 'opt_2', 'text' => 'B', 'is_correct' => true],
                    ['id' => 'opt_3', 'text' => 'C', 'is_correct' => true],
                ];

                public string $quizType = 'multiple';

                public function getOptionsAttribute(): ?array
                {
                    return $this->options;
                }

                public function getTypeAttribute(): string
                {
                    return $this->quizType;
                }
            };

            expect($this->action->execute($entry, ['opt_1', 'opt_2', 'opt_3']))->toBeTrue();
        });
    });

    describe('edge cases', function () {
        it('returns false when options is null', function () {
            $entry = new class extends QuizEntry {
                public ?array $options = null;

                public function getOptionsAttribute(): ?array
                {
                    return $this->options;
                }
            };

            expect($this->action->execute($entry, ['opt_1']))->toBeFalse();
        });

        it('returns false when options is empty array', function () {
            $entry = new class extends QuizEntry {
                public array $options = [];

                public function getOptionsAttribute(): ?array
                {
                    return $this->options;
                }
            };

            expect($this->action->execute($entry, ['opt_1']))->toBeFalse();
        });

        it('returns false when selected options contain unknown IDs', function () {
            $entry = new class extends QuizEntry {
                public array $options = [
                    ['id' => 'opt_1', 'text' => 'Correct', 'is_correct' => true],
                ];

                public string $quizType = 'single';

                public function getOptionsAttribute(): ?array
                {
                    return $this->options;
                }

                public function getTypeAttribute(): string
                {
                    return $this->quizType;
                }
            };

            expect($this->action->execute($entry, ['opt_999']))->toBeFalse();
        });

        it('handles options with mixed case is_correct values', function () {
            $entry = new class extends QuizEntry {
                public array $options = [
                    ['id' => 'opt_1', 'text' => 'Correct', 'is_correct' => true],
                    ['id' => 'opt_2', 'text' => 'Wrong', 'is_correct' => false],
                ];

                public string $quizType = 'single';

                public function getOptionsAttribute(): ?array
                {
                    return $this->options;
                }

                public function getTypeAttribute(): string
                {
                    return $this->quizType;
                }
            };

            expect($this->action->execute($entry, ['opt_1']))->toBeTrue();
        });
    });
});
