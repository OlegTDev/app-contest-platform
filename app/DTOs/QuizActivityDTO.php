<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class QuizActivityDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public string|null $description,
        public bool $isVisible,
        public string $createdAt,
    ) {}

    /**
     * Map a collection of quiz entries to DTOs.
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuizEntry> $entries
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function collection(\Illuminate\Support\Collection $entries): \Illuminate\Support\Collection
    {
        return $entries->map(fn (\App\Models\QuizEntry $entry): self => self::from($entry));
    }

    public static function from(\App\Models\QuizEntry $entry): self
    {
        return new self(
            id: $entry->id,
            title: $entry->title,
            description: $entry->description,
            isVisible: $entry->isVisible(),
            createdAt: $entry->created_at !== null ? $entry->created_at->format('Y-m-d H:i') : '',
        );
    }
}
