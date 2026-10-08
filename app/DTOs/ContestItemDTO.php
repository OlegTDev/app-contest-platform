<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * @phpstan-type SelectOptionType array{value: string, label: string}
 */
final readonly class ContestItemDTO
{
    /**
     * @param array<int, SelectOptionType> $contestTypes
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $type,
        public string|null $description,
        public string|null $startAt,
        public string|null $endAt,
        public int $quizEntryCount,
        public int $entryCount,
        public string $createdAt,
        public array $contestTypes = [],
    ) {}

    /**
     * Map a collection of contests to DTOs.
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\Contest> $contests
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function collection(
        \Illuminate\Support\Collection $contests,
        /** @var array<int, SelectOptionType> */
        array $contestTypes = [],
    ): \Illuminate\Support\Collection {
        return $contests->map(fn (\App\Models\Contest $contest): self => self::from($contest, $contestTypes));
    }

    /**
     * @param array<int, SelectOptionType> $contestTypes
     */
    public static function from(\App\Models\Contest $contest, array $contestTypes = []): self
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
