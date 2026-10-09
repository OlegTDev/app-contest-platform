<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * @phpstan-type QuizActivityType array{id: int, title: string, description: string|null, is_visible: bool, created_at: string}
 * @phpstan-type VotingActivityType array{id: int, title: string, description: string|null, author_name: string|null, author_department: string|null, votes_count: int, is_voted: bool, fields_data: array<string, mixed>|null, media: array<int, array{id: int, file_name: string, file_url: string, file_type: string, file_extension: string, file_size: int|null, is_main: bool, created_at: string}>, created_at: string}
 * @phpstan-type LeaderboardType array{id: int, title: string, votes_count: int, author_name: string|null}
 */
final readonly class ContestShowDTO
{
    /**
     * @param  array<QuizActivityType>  $quizActivities
     * @param  array<VotingActivityType>  $votingActivities
     * @param  array<LeaderboardType>  $leaderboard
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $type,
        public string $status,
        public ?string $description,
        public ?string $startAt,
        public ?string $endAt,
        public bool $isActive,
        public bool $isTimeActive,
        public string $createdAt,
        /** @var array<QuizActivityType> */
        public array $quizActivities = [],
        /** @var array<VotingActivityType> */
        public array $votingActivities = [],
        /** @var array<LeaderboardType> */
        public array $leaderboard = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
            'start_at' => $this->startAt,
            'end_at' => $this->endAt,
            'is_active' => $this->isActive,
            'is_time_active' => $this->isTimeActive,
            'activities' => array_merge($this->quizActivities, $this->votingActivities),
            'leaderboard' => $this->leaderboard,
            'created_at' => $this->createdAt,
        ];
    }
}
