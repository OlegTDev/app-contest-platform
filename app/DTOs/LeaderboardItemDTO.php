<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class LeaderboardItemDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public int $votesCount,
        public string|null $authorName,
    ) {}

    /**
     * Map a collection of entries to leaderboard DTOs.
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\ContestEntry> $entries
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function collection(\Illuminate\Support\Collection $entries): \Illuminate\Support\Collection
    {
        return $entries->map(fn (\App\Models\ContestEntry $entry): self => self::from($entry));
    }

    public static function from(\App\Models\ContestEntry $entry): self
    {
        return new self(
            id: $entry->id,
            title: $entry->title,
            votesCount: $entry->votes_count,
            authorName: $entry->author_name,
        );
    }
}
