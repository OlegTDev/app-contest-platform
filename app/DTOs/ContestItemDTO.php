<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Contest;
use Illuminate\Support\Collection;

/**
 * @phpstan-type SelectOptionType array{value: string, label: string}
 */
final readonly class ContestItemDTO
{
    /**
     * @param  array<int, SelectOptionType>  $contestTypes
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $type,
        public ?string $description,
        public ?string $startAt,
        public ?string $endAt,
        public int $quizEntryCount,
        public int $entryCount,
        public string $createdAt,
        public array $contestTypes = [],
    ) {}

    /**
     * Map a collection of contests to DTOs.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Contest>  $contests
     * @param  array<int, SelectOptionType>  $contestTypes
     * @return Collection<int, self>
     */
    public static function collection(
        Collection $contests,
        array $contestTypes = [],
    ): Collection {
        return $contests->map(fn (Contest $contest): self => self::from($contest, $contestTypes));
    }

    /**
     * @param  array<int, SelectOptionType>  $contestTypes
     */
    public static function from(Contest $contest, array $contestTypes = []): self
    {
        return new self(
            id: $contest->id,
            title: $contest->title,
            type: $contest->type->value,
            description: $contest->description,
            startAt: $contest->start_at?->format('Y-m-d H:i'),
            endAt: $contest->end_at?->format('Y-m-d H:i'),
            quizEntryCount: $contest->quizEntries->count(),
            entryCount: $contest->entries->count(),
            createdAt: $contest->created_at->format('Y-m-d H:i'),
            contestTypes: $contestTypes,
        );
    }
}
