<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\AdminContestDTO;
use App\DTOs\AdminQuizEntryDTO;
use App\DTOs\AdminVotingEntryDTO;
use App\DTOs\ContestItemDTO;
use App\DTOs\ContestShowDTO;
use App\DTOs\LeaderboardItemDTO;
use App\DTOs\QuizActivityDTO;
use App\DTOs\VotingActivityDTO;
use App\Models\Contest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Throwable;

final readonly class ContestService
{
    public function __construct(
        private readonly UserContestActivityService $userActivity,
    ) {}

    /**
     * Get active published contests for the public index page.
     *
     * @return Collection<int, ContestItemDTO>
     */
    public function getActiveContests(): Collection
    {
        $contests = Contest::published()
            ->with(['quizEntries', 'entries'])
            ->orderByDesc('created_at')
            ->get();

        return ContestItemDTO::collection($contests);
    }

    /**
     * Get contest data for the public show page.
     *
     * @param  array<int, int>  $userVoteEntryIds
     * @param  array<int, int>  $viewedEntryIds
     * @param  array<int, int>  $answeredQuizEntryIds
     */
    public function getPublicShowData(Contest $contest, array $userVoteEntryIds = [], array $viewedEntryIds = [], array $answeredQuizEntryIds = []): ContestShowDTO
    {
        $this->loadContestRelations($contest);

        $quizEntries = $contest->quizEntries ?? collect();
        $entries = $contest->entries ?? collect();

        $quizActivities = QuizActivityDTO::collection($quizEntries, $answeredQuizEntryIds)->toArray();
        $votingActivities = VotingActivityDTO::collection($entries, $userVoteEntryIds, $viewedEntryIds)->toArray();

        return new ContestShowDTO(
            id: $contest->id,
            title: $contest->title,
            type: $contest->type->value,
            status: $contest->status,
            slug: $contest->slug,
            description: $contest->description,
            startAt: $contest->start_at?->format('Y-m-d H:i'),
            endAt: $contest->end_at?->format('Y-m-d H:i'),
            isActive: $contest->isActive(),
            isTimeActive: app(TimeWindowService::class)->isContestActive($contest),
            createdAt: $contest->created_at->format('Y-m-d H:i'),
            isArchived: $contest->status === 'archived',
            quizActivities: $quizActivities,
            votingActivities: $votingActivities,
            leaderboard: [],
        );
    }

    /**
     * Get contest data for the admin show page.
     */
    public function getAdminShowData(Contest $contest, int $userId): ContestShowDTO
    {
        $this->loadContestRelations($contest, withMedia: false);

        $quizActivities = [];
        $votingActivities = [];

        if ($contest->type->value === 'quiz') {
            $quizActivities = $contest->quizEntries
                ->map(fn ($entry) => [
                    'id' => $entry->id,
                    'type' => 'quiz',
                    'title' => $entry->title,
                    'description' => $entry->description,
                    'question' => $entry->question,
                    'options' => $entry->options,
                    'quiz_type' => $entry->type,
                    'is_scheduled' => $entry->isScheduled(),
                    'is_visible' => $entry->isVisible(),
                    'created_at' => $entry->created_at?->format('Y-m-d H:i') ?? '',
                ])
                ->toArray();
        } else {
            $votingActivities = $contest->entries
                ->map(fn ($entry) => [
                    'id' => $entry->id,
                    'type' => 'voting',
                    'title' => $entry->title,
                    'description' => $entry->description,
                    'author_name' => $entry->author_name,
                    'author_department' => $entry->author_department,
                    'votes_count' => $entry->votes_count,
                    'is_voted' => false,
                    'fields_data' => null,
                    'media' => [],
                    'created_at' => $entry->created_at?->format('Y-m-d H:i') ?? '',
                ])
                ->toArray();
        }

        return new ContestShowDTO(
            id: $contest->id,
            title: $contest->title,
            type: $contest->type->value,
            status: $contest->status,
            slug: $contest->slug,
            description: $contest->description,
            startAt: $contest->start_at?->format('Y-m-d H:i'),
            endAt: $contest->end_at?->format('Y-m-d H:i'),
            isActive: $contest->isActive(),
            isTimeActive: app(TimeWindowService::class)->isContestActive($contest),
            createdAt: $contest->created_at->format('Y-m-d H:i'),
            isArchived: $contest->status === 'archived',
            quizActivities: $quizActivities,
            votingActivities: $votingActivities,
            leaderboard: [],
        );
    }

    /**
     * Get contests for the admin index page with pagination.
     *
     * @param  array<string, string|null>  $filters
     */
    public function getAdminContests(array $filters, int $userId): LengthAwarePaginator
    {
        $query = Contest::with(['quizEntries', 'entries', 'author'])
            ->orderByDesc('created_at');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['search'])) {
            $query->where('title', 'ilike', '%'.$filters['search'].'%');
        }

        $canManage = $this->userCanManage($userId);

        /** @var LengthAwarePaginator<int, Contest> $paginator */
        $paginator = $query->paginate(15);

        return $paginator->through(
            fn (Contest $contest) => AdminContestDTO::fromArray([
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
                'can_manage' => $canManage,
                'created_at' => $contest->created_at->format('Y-m-d H:i'),
            ])->toArray()
        );
    }

    /**
     * Get contest data for the admin edit page.
     */
    public function getAdminEditData(Contest $contest): array
    {
        return [
            'id' => $contest->id,
            'title' => $contest->title,
            'type' => $contest->type->value,
            'status' => $contest->status,
            'slug' => $contest->slug,
            'description' => $contest->description,
            'start_at' => $contest->start_at?->format('Y-m-d H:i:s'),
            'end_at' => $contest->end_at?->format('Y-m-d H:i:s'),
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
                ->reorder()
                ->whereNotNull('votes_count')
                ->orderByDesc('votes_count')
                ->limit(50)
                ->get()
        )->toArray();
    }

    /**
     * Load contest relations for public show.
     */
    private function loadContestRelations(Contest $contest, bool $withMedia = true): void
    {
        $with = [
            'quizEntries' => fn ($query) => $query->active()->orderBy('sort_order'),
        ];

        if ($withMedia) {
            $with['quizEntries.media'] = [];
            $with['entries.media'] = [];
        }

        $with['entries'] = fn ($query) => $query->orderBy('id')->with('user');

        $contest->load($with);
    }

    /**
     * Check if a user can manage any contest (admin/moderator).
     */
    private function userCanManage(int $userId): bool
    {
        $user = User::find($userId);

        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $user->isModerator();
    }
}
