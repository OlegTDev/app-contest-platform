<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Represents a single contest in the admin index page.
 */
final readonly class AdminContestDTO
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
}
