<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * @phpstan-type MediaItemType array{id: int, file_name: string, file_url: string, file_type: string, file_extension: string, file_size: int|null, is_main: bool, created_at: string}
 */
final readonly class VotingActivityDTO
{
    /**
     * @param array<string, mixed>|null $fieldsData
     * @param array<MediaItemType> $media
     */
    public function __construct(
        public int $id,
        public string $title,
        public string|null $description,
        public string|null $authorName,
        public string|null $authorDepartment,
        public int $votesCount,
        public bool $isVoted,
        public array|null $fieldsData,
        /** @var array<MediaItemType> */
        public array $media,
        public string $createdAt,
    ) {}

    /**
     * Map a collection of contest entries to DTOs.
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\ContestEntry> $entries
     * @param array<int, int> $userVoteEntryIds
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function collection(
        \Illuminate\Support\Collection $entries,
        array $userVoteEntryIds = [],
    ): \Illuminate\Support\Collection {
        return $entries->map(fn (\App\Models\ContestEntry $entry): self => self::from($entry, $userVoteEntryIds));
    }

    /**
     * @param array<int, int> $userVoteEntryIds
     */
    public static function from(\App\Models\ContestEntry $entry, array $userVoteEntryIds = []): self
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Media> $media */
        $media = $entry->media;

        return new self(
            id: $entry->id,
            title: $entry->title,
            description: $entry->description,
            authorName: $entry->author_name,
            authorDepartment: $entry->author_department,
            votesCount: $entry->votes_count,
            isVoted: in_array($entry->id, $userVoteEntryIds, strict: true),
            fieldsData: $entry->fields_data !== null ? (array) $entry->fields_data : null,
            media: MediaDTO::collection($media)->toArray(),
            createdAt: $entry->created_at !== null ? $entry->created_at->format('Y-m-d H:i') : '',
        );
    }
}
