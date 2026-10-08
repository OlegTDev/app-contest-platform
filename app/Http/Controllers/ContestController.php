<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ContestType;
use App\Http\Requests\ContestRequest;
use App\Models\Contest;
use App\Services\ContestService;
use Illuminate\Http\RedirectResponse;
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
    public function publicShow(Request $request, Contest $contest): Response
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

    /**
     * Админка — список конкурсов.
     */
    public function index(Request $request): Response
    {
        $contests = $this->contestService->getAdminContests([
            'status' => $request->input('status'),
            'type' => $request->input('type'),
            'search' => $request->input('search'),
        ]);

        return Inertia::render('contests/index', [
            'contests' => $contests,
            'filters' => [
                'status' => $request->input('status'),
                'type' => $request->input('type'),
                'search' => $request->input('search'),
            ],
        ]);
    }

    /**
     * Форма создания конкурса.
     */
    public function create(): Response
    {
        return Inertia::render('contests/create', [
            'contestTypes' => ContestType::selectOptions(),
        ]);
    }

    /**
     * Сохранение нового конкурса.
     */
    public function store(ContestRequest $request): RedirectResponse
    {
        Contest::create($request->validated());

        return to_route('contest.index')->with('success', 'Contest created successfully.');
    }

    /**
     * Админка — страница конкурса.
     */
    public function show(Request $request, Contest $contest): Response
    {
        $data = $this->contestService->getAdminShowData($contest);

        return Inertia::render('contests/show', [
            'contest' => $data,
            'is_owner' => $request->user()->id === $contest->user_id,
        ]);
    }

    /**
     * Форма редактирования конкурса.
     */
    public function edit(Request $request, Contest $contest): Response
    {
        $this->authorizeOwner($request->user(), $contest);

        return Inertia::render('contests/edit', [
            'contest' => $this->contestService->getAdminEditData($contest),
            'contestTypes' => ContestType::selectOptions(),
        ]);
    }

    /**
     * Обновление конкурса.
     */
    public function update(ContestRequest $request, Contest $contest): RedirectResponse
    {
        $this->authorizeOwner($request->user(), $contest);

        $contest->update($request->validated());

        return to_route('contest.show', $contest)->with('success', 'Contest updated successfully.');
    }

    /**
     * Удаление конкурса.
     */
    public function destroy(Request $request, Contest $contest): RedirectResponse
    {
        $this->authorizeOwner($request->user(), $contest);

        $contest->delete();

        return to_route('contest.index')->with('success', 'Contest deleted successfully.');
    }

    /**
     * Обновление статуса конкурса.
     */
    public function updateStatus(Request $request, Contest $contest): RedirectResponse
    {
        $this->authorizeOwner($request->user(), $contest);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:draft,published,paused,closed'],
        ]);

        $contest->update(['status' => $validated['status']]);

        return back()->with('success', 'Contest status updated successfully.');
    }

    /**
     * Проверить права владельца конкурса.
     */
    private function authorizeOwner(\Illuminate\Contracts\Auth\Authenticatable $user, Contest $contest): void
    {
        abort_unless($user->id === $contest->user_id, 403, 'You do not have permission to edit this contest.');
    }
}
