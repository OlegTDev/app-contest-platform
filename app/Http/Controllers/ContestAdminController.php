<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ContestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContestAdminController extends Controller
{
    public function __construct(
        private readonly ContestService $contestService,
    ) {}

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
            'contestTypes' => \App\Enums\ContestType::selectOptions(),
        ]);
    }

    /**
     * Сохранение нового конкурса.
     */
    public function store(\App\Http\Requests\ContestRequest $request): RedirectResponse
    {
        \App\Models\Contest::create($request->validated());

        return to_route('contest.index')->with('success', 'Contest created successfully.');
    }

    /**
     * Админка — страница конкурса.
     */
    public function show(Request $request, \App\Models\Contest $contest): Response
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
    public function edit(Request $request, \App\Models\Contest $contest): Response
    {
        $this->authorizeOwner($request->user(), $contest);

        return Inertia::render('contests/edit', [
            'contest' => $this->contestService->getAdminEditData($contest),
            'contestTypes' => \App\Enums\ContestType::selectOptions(),
        ]);
    }

    /**
     * Обновление конкурса.
     */
    public function update(\App\Http\Requests\ContestRequest $request, \App\Models\Contest $contest): RedirectResponse
    {
        $this->authorizeOwner($request->user(), $contest);

        $contest->update($request->validated());

        return to_route('contest.show', $contest)->with('success', 'Contest updated successfully.');
    }

    /**
     * Удаление конкурса.
     */
    public function destroy(Request $request, \App\Models\Contest $contest): RedirectResponse
    {
        $this->authorizeOwner($request->user(), $contest);

        $contest->delete();

        return to_route('contest.index')->with('success', 'Contest deleted successfully.');
    }

    /**
     * Обновление статуса конкурса.
     */
    public function updateStatus(Request $request, \App\Models\Contest $contest): RedirectResponse
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
    private function authorizeOwner(\Illuminate\Contracts\Auth\Authenticatable $user, \App\Models\Contest $contest): void
    {
        abort_unless($user->id === $contest->user_id, 403, 'You do not have permission to edit this contest.');
    }
}
