<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\ContestEntry;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class LeaderboardItemDTO implements Arrayable
{
    public function __construct(
        public int $id,
        public string $title,
        public int $votesCount,
        public ?string $authorName,
    ) {}

    /**
     * Map a collection of entries to leaderboard DTOs.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, ContestEntry>  $entries
     * @return Collection<int, self>
     */
    public static function collection(Collection $entries): Collection
    {
        return $entries->map(fn (ContestEntry $entry): self => self::from($entry));
    }

    public static function from(ContestEntry $entry): self
    {
        return new self(
            id: $entry->id,
            title: $entry->title,
            votesCount: $entry->votes_count,
            authorName: $entry->author_name,
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
            'votes_count' => $this->votesCount,
            'author_name' => $this->authorName,
        ];
    }
}
