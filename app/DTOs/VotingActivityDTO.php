<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\ContestEntry;
use App\Models\Media;
use Illuminate\Support\Collection;

/**
 * @phpstan-type MediaItemType array{id: int, file_name: string, file_url: string, file_type: string, file_extension: string, file_size: int|null, is_main: bool, created_at: string}
 */
final readonly class VotingActivityDTO
{
    /**
     * @param  array<string, mixed>|null  $fieldsData
     * @param  array<MediaItemType>  $media
     */
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public ?string $authorName,
        public ?string $authorDepartment,
        public int $votesCount,
        public bool $isVoted,
        public ?array $fieldsData,
        /** @var array<MediaItemType> */
        public array $media,
        public string $createdAt,
        public string $type = 'voting',
    ) {}

    /**
     * Map a collection of contest entries to DTOs.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, ContestEntry>  $entries
     * @param  array<int, int>  $userVoteEntryIds
     * @return Collection<int, self>
     */
    public static function collection(
        Collection $entries,
        array $userVoteEntryIds = [],
    ): Collection {
        return $entries->map(fn (ContestEntry $entry): self => self::from($entry, $userVoteEntryIds));
    }

    /**
     * @param  array<int, int>  $userVoteEntryIds
     */
    public static function from(ContestEntry $entry, array $userVoteEntryIds = []): self
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Media> $media */
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
            type: 'voting',
        );
    }
}
