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
    /**
     * @param  array<int, array{id: string, text: string, is_correct: bool}>|null  $options
     * @param  array<int, array{id: int, file_name: string, file_url: string, file_type: string, file_extension: string, file_size: int|null, is_main: bool}>  $media
     */
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public ?string $question,
        public ?array $options,
        public ?string $quizType,
        public bool $isVisible,
        public bool $isAnswered,
        public array $media,
        public string $createdAt,
        public string $type = 'quiz',
    ) {}

    /**
     * Map a collection of quiz entries to DTOs.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, QuizEntry>  $entries
     * @param  array<int, int>  $answeredEntryIds
     * @return Collection<int, self>
     */
    public static function collection(Collection $entries, array $answeredEntryIds = []): Collection
    {
        return $entries->map(fn (QuizEntry $entry): self => self::from($entry, $answeredEntryIds));
    }

    /**
     * Create DTO from model.
     */
    public static function from(QuizEntry $entry, array $answeredEntryIds = []): self
    {
        $media = $entry->media->map(function ($m) {
            return [
                'id' => $m->id,
                'file_name' => $m->file_name,
                'file_url' => $m->file_url,
                'file_type' => $m->file_type,
                'file_extension' => $m->file_extension,
                'file_size' => $m->file_size,
                'is_main' => $m->is_main,
            ];
        })->toArray();

        return new self(
            id: $entry->id,
            title: $entry->title,
            description: $entry->description,
            question: $entry->question,
            options: $entry->options,
            quizType: $entry->type,
            isVisible: method_exists($entry, 'isVisible') ? $entry->isVisible() : true,
            isAnswered: in_array($entry->id, $answeredEntryIds, true),
            media: $media,
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
            'question' => $this->question,
            'options' => $this->options,
            'quizType' => $this->quizType,
            'is_visible' => $this->isVisible,
            'is_answered' => $this->isAnswered,
            'media' => $this->media,
            'created_at' => $this->createdAt,
            'type' => $this->type,
        ];
    }
}
