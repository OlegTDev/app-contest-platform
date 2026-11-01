<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Represents a single voting entry in the admin show page.
 */
final readonly class AdminVotingEntryDTO
{
    public function __construct(
        public int $id,
        public string $type,
        public string $title,
        public ?string $description,
        public ?string $authorName,
        public ?string $authorDepartment,
        public int $votesCount,
        public string $createdAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            type: 'voting',
            title: $data['title'],
            description: $data['description'] ?? null,
            authorName: $data['author_name'] ?? null,
            authorDepartment: $data['author_department'] ?? null,
            votesCount: (int) ($data['votes_count'] ?? 0),
            createdAt: $data['created_at'] ?? '',
        );
    }
}
