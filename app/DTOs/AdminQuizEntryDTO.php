<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Represents a single contest entry in the admin show page.
 */
final readonly class AdminQuizEntryDTO
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public int $id,
        public string $type,
        public string $title,
        public ?string $description,
        public ?string $question,
        public array $options,
        public string $quizType,
        public bool $isScheduled,
        public bool $isVisible,
        public string $createdAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            type: 'quiz',
            title: $data['title'],
            description: $data['description'] ?? null,
            question: $data['question'] ?? null,
            options: $data['options'] ?? [],
            quizType: $data['quiz_type'] ?? 'single',
            isScheduled: (bool) ($data['is_scheduled'] ?? false),
            isVisible: (bool) ($data['is_visible'] ?? true),
            createdAt: $data['created_at'] ?? '',
        );
    }
}
