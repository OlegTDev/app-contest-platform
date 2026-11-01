<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Represents a single contest in the admin index page.
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class AdminContestDTO implements Arrayable
{
    public function __construct(
        public int $id,
        public string $title,
        public string $type,
        public string $status,
        public ?string $description,
        public ?string $startAt,
        public ?string $endAt,
        public bool $isActive,
        public int $quizEntryCount,
        public int $entryCount,
        public AdminContestAuthorDTO $author,
        public bool $canManage,
        public string $createdAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            title: (string) $data['title'],
            type: (string) $data['type'],
            status: (string) $data['status'],
            description: $data['description'] ?? null,
            startAt: $data['start_at'] ?? null,
            endAt: $data['end_at'] ?? null,
            isActive: (bool) $data['is_active'],
            quizEntryCount: (int) ($data['quiz_entry_count'] ?? 0),
            entryCount: (int) ($data['entry_count'] ?? 0),
            author: AdminContestAuthorDTO::fromArray($data['author'] ?? []),
            canManage: (bool) ($data['can_manage'] ?? false),
            createdAt: (string) $data['created_at'],
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
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
            'start_at' => $this->startAt,
            'end_at' => $this->endAt,
            'is_active' => $this->isActive,
            'quiz_entry_count' => $this->quizEntryCount,
            'entry_count' => $this->entryCount,
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ],
            'can_manage' => $this->canManage,
            'created_at' => $this->createdAt,
        ];
    }
}
