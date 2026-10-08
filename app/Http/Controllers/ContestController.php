<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ContestService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContestController extends Controller
{
    public function __construct(
        private readonly ContestService $contestService,
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
    public function publicShow(Request $request, \App\Models\Contest $contest): Response
    {
        abort_unless($contest->status === 'published', 404);

        $user = $request->user();
        $userVoteEntryIds = $user !== null
            ? $this->contestService->getUserVoteEntryIds($contest, $user)
            : [];

        $data = $this->contestService->getPublicShowData($contest, $userVoteEntryIds);

        return Inertia::render('contests/PublicShow', [
            'contest' => $data->toArray(),
            'leaderboard' => $this->contestService->getLeaderboard($contest),
        ]);
    }
}
