<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\QuizEntry;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class QuizActivityDTO implements Arrayable
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public bool $isVisible,
        public string $createdAt,
        public string $type = 'quiz',
    ) {}

    /**
     * Map a collection of quiz entries to DTOs.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, QuizEntry>  $entries
     * @return Collection<int, self>
     */
    public static function collection(Collection $entries): Collection
    {
        return $entries->map(fn (QuizEntry $entry): self => self::from($entry));
    }

    /**
     * Create DTO from model.
     */
    public static function from(QuizEntry $entry): self
    {
        return new self(
            id: $entry->id,
            title: $entry->title,
            description: $entry->description,
            isVisible: $entry->isVisible(),
            createdAt: $entry->created_at !== null ? $entry->created_at->format('Y-m-d H:i') : '',
            type: 'quiz',
        );
    }

    /**
     * Convert DTO to array with snake_case keys for frontend compatibility.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'is_visible' => $this->isVisible,
            'created_at' => $this->createdAt,
            'type' => $this->type,
        ];
    }
}
