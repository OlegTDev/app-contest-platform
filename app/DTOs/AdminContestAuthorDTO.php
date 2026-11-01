<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Represents the author of a contest in the admin index page.
 */
final readonly class AdminContestAuthorDTO
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: (string) ($data['name'] ?? 'Unknown'),
        );
    }
}
