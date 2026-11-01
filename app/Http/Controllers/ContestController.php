<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Services\ContestService;
use App\Services\UserContestActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContestController extends Controller
{
    public function __construct(
        private readonly ContestService $contestService,
        private readonly UserContestActivityService $userActivity,
    ) {}

    /**
     * Публичная страница — активные конкурсы.
     */
    public function publicIndex(): Response
    {
        $contests = $this->contestService->getActiveContests();

        return Inertia::render('contests/PublicIndex', [
            'contests' => $contests->toArray(),
        ]);
    }

    /**
     * Публичная страница конкурса.
     */
    public function publicShow(Request $request, Contest $contest): Response
    {
        abort_unless(in_array($contest->status, ['published', 'archived', 'closed', 'paused'], strict: true), 404);

        $user = $request->user();
        $userVoteEntryIds = $user !== null
            ? $this->userActivity->getUserVoteEntryIds($user, $contest->id)->toArray()
            : [];

        $viewedEntryIds = $user !== null
            ? $this->userActivity->getViewedEntryIds($user, $contest->id)->toArray()
            : [];

        $answeredQuizEntryIds = $user !== null
            ? $this->userActivity->getAnsweredQuizEntryIds($user, $contest->id)->toArray()
            : [];

        $data = $this->contestService->getPublicShowData($contest, $userVoteEntryIds, $viewedEntryIds, $answeredQuizEntryIds);

        return Inertia::render('contests/PublicShow', [
            'contest' => $data->toArray(),
            'leaderboard' => $this->contestService->getLeaderboard($contest),
        ]);
    }

    /**
     * Mark an entry as viewed.
     */
    public function markAsViewed(Request $request, Contest $contest, int $entryId): RedirectResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $this->userActivity->markEntryAsViewed($user, $contest->id, $entryId);
        }

        return back();
    }
}
