<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\ContestEntry;
use App\Models\Media;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class VotingActivityDTO implements Arrayable
{
    /**
     * @param  array<string, mixed>|null  $fieldsData
     * @param  array<int, MediaDTO>  $media
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
        /** @var array<int, MediaDTO> */
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
            media: MediaDTO::collection($media)->all(),
            createdAt: $entry->created_at !== null ? $entry->created_at->format('Y-m-d H:i') : '',
            type: 'voting',
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
            'author_name' => $this->authorName,
            'author_department' => $this->authorDepartment,
            'votes_count' => $this->votesCount,
            'is_voted' => $this->isVoted,
            'fields_data' => $this->fieldsData,
            'media' => array_map(fn (MediaDTO $m): array => $m->toArray(), $this->media),
            'created_at' => $this->createdAt,
            'type' => $this->type,
        ];
    }
}
