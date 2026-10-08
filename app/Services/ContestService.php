<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ContestItemDTO;
use App\DTOs\ContestShowDTO;
use App\DTOs\LeaderboardItemDTO;
use App\DTOs\QuizActivityDTO;
use App\DTOs\VotingActivityDTO;
use App\Models\Contest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final readonly class ContestService
{
    /**
     * Get active published contests for the public index page.
     *
     * @return Collection<int, ContestItemDTO>
     */
    public function getActiveContests(): Collection
    {
        $contests = Contest::where('status', 'published')
            ->where(function (Builder $query): void {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->with(['quizEntries', 'entries'])
            ->orderByDesc('created_at')
            ->get();

        return ContestItemDTO::collection($contests);
    }

    /**
     * Get contest data for the public show page.
     *
     * @param array<int, int> $userVoteEntryIds
     */
    public function getPublicShowData(Contest $contest, array $userVoteEntryIds = []): ContestShowDTO
    {
        $this->loadContestRelations($contest);

        $isTimeActive = $this->isTimeActive($contest);
        $quizActivities = QuizActivityDTO::collection($contest->quizEntries)->toArray();
        $votingActivities = VotingActivityDTO::collection($contest->entries, $userVoteEntryIds)->toArray();

        return new ContestShowDTO(
            id: $contest->id,
            title: $contest->title,
            type: $contest->type->value,
            status: $contest->status,
            description: $contest->description,
            startAt: $contest->start_at?->format('Y-m-d H:i'),
            endAt: $contest->end_at?->format('Y-m-d H:i'),
            isActive: $contest->isActive(),
            isTimeActive: $isTimeActive,
            createdAt: $contest->created_at->format('Y-m-d H:i'),
            quizActivities: $quizActivities,
            votingActivities: $votingActivities,
            leaderboard: [],
        );
    }

    /**
     * Get contest data for the admin show page.
     *
     * @return array<string, mixed>
     */
    public function getAdminShowData(Contest $contest): array
    {
        $this->loadContestRelations($contest, withMedia: false);

        $activities = match ($contest->type->value) {
            'quiz' => $contest->quizEntries->map(fn ($entry): array => [
                'id' => $entry->id,
                'type' => 'quiz',
                'title' => $entry->title,
                'description' => $entry->description,
                'is_scheduled' => $entry->isScheduled(),
                'is_visible' => $entry->isVisible(),
                'created_at' => $entry->created_at->format('Y-m-d H:i'),
            ])->toArray(),
            default => $contest->entries->map(fn ($entry): array => [
                'id' => $entry->id,
                'type' => 'voting',
                'title' => $entry->title,
                'description' => $entry->description,
                'author_name' => $entry->author_name,
                'author_department' => $entry->author_department,
                'votes_count' => $entry->votes_count,
                'created_at' => $entry->created_at->format('Y-m-d H:i'),
            ])->toArray(),
        };

        return [
            'id' => $contest->id,
            'title' => $contest->title,
            'type' => $contest->type->value,
            'status' => $contest->status,
            'description' => $contest->description,
            'start_at' => $contest->start_at?->format('Y-m-d H:i'),
            'end_at' => $contest->end_at?->format('Y-m-d H:i'),
            'is_active' => $contest->isActive(),
            'activities' => $activities,
            'created_at' => $contest->created_at->format('Y-m-d H:i'),
        ];
    }

    /**
     * Get contests for the admin index page with pagination.
     *
     * @param array<string, string|null> $filters
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<array{id: int, title: string, type: string, status: string, description: string|null, start_at: string|null, end_at: string|null, is_active: bool, quiz_entry_count: int, entry_count: int, author: array{id: int, name: string}, is_owner: bool, created_at: string}>
     */
    public function getAdminContests(array $filters, int $userId): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Contest::with(['quizEntries', 'entries'])
            ->orderByDesc('created_at');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['search'])) {
            $query->where('title', 'ilike', '%' . $filters['search'] . '%');
        }

        $paginator = $query->paginate(15);

        $paginator->getCollection()->transform(function (Contest $contest) use ($userId): array {
            return [
                'id' => $contest->id,
                'title' => $contest->title,
                'type' => $contest->type->value,
                'status' => $contest->status,
                'description' => $contest->description,
                'start_at' => $contest->start_at?->format('Y-m-d H:i'),
                'end_at' => $contest->end_at?->format('Y-m-d H:i'),
                'is_active' => $contest->isActive(),
                'quiz_entry_count' => $contest->quizEntries->count(),
                'entry_count' => $contest->entries->count(),
                'author' => [
                    'id' => $contest->user_id,
                    'name' => $contest->author !== null ? $contest->author->name : 'Unknown',
                ],
                'is_owner' => $contest->user_id === $userId,
                'created_at' => $contest->created_at->format('Y-m-d H:i'),
            ];
        });

        return $paginator;
    }

    /**
     * Get contest data for the admin edit page.
     *
     * @return array<string, mixed>
     */
    public function getAdminEditData(Contest $contest): array
    {
        return [
            'id' => $contest->id,
            'title' => $contest->title,
            'type' => $contest->type->value,
            'status' => $contest->status,
            'description' => $contest->description,
            'start_at' => $contest->start_at?->format('Y-m-d H:i'),
            'end_at' => $contest->end_at?->format('Y-m-d H:i'),
            'project_schema' => $contest->project_schema ?? [],
        ];
    }

    /**
     * Get leaderboard for a voting contest.
     *
     * @return array<int, array{id: int, title: string, votes_count: int, author_name: string|null}>
     */
    public function getLeaderboard(Contest $contest): array
    {
        if ($contest->type->value !== 'voting') {
            return [];
        }

        return LeaderboardItemDTO::collection(
            $contest->entries()
                ->whereNotNull('votes_count')
                ->orderByDesc('votes_count')
                ->limit(50)
                ->get()
        )->toArray();
    }

    /**
     * Get user vote entry IDs for a contest.
     *
     * @return array<int, int>
     */
    public function getUserVoteEntryIds(Contest $contest, \Illuminate\Contracts\Auth\Authenticatable $user): array
    {
        /** @var \App\Models\User $authUser */
        $authUser = $user instanceof \App\Models\User
            ? $user
            : throw new \InvalidArgumentException('Invalid user type');

        /** @var array<int, int> $result */
        $result = $authUser->votes()
            ->where('contest_id', $contest->id)
            ->pluck('entry_id')
            ->toArray();

        return $result;
    }

    /**
     * Load contest relations for public show.
     */
    private function loadContestRelations(Contest $contest, bool $withMedia = true): void
    {
        $contest->load([
            'quizEntries' => function ($query): void {
                $query->where(function ($q): void {
                    $q->whereNull('show_from')
                        ->orWhere('show_from', '<=', now());
                })->where(function ($q): void {
                    $q->whereNull('show_until')
                        ->orWhere('show_until', '>=', now());
                })->orderBy('sort_order');
            },
            'entries' => function ($query): void {
                $query->orderBy('id');
            },
        ]);

        if ($withMedia) {
            $contest->load('entries.media');
        }
    }

    /**
     * Check if contest is within its time window.
     */
    private function isTimeActive(Contest $contest): bool
    {
        $now = now();

        if ($contest->start_at && $now->lt($contest->start_at)) {
            return false;
        }

        if ($contest->end_at && $now->gt($contest->end_at)) {
            return false;
        }

        return true;
    }
}
