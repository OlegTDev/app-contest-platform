<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contest;
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
        ], $this->user()->id);

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
        Contest::create($request->validated());

        return to_route('admin.contests.index')->with('success', 'Contest created successfully.');
    }

    /**
     * Админка — страница конкурса.
     */
    public function show(Contest $contest): Response
    {
        $data = $this->contestService->getAdminShowData($contest);

        return Inertia::render('contests/show', [
            'contest' => $data,
            'is_owner' => $this->user()->id === $contest->user_id,
        ]);
    }

    /**
     * Форма редактирования конкурса.
     */
    public function edit(Contest $contest): Response
    {
        abort_unless($this->user()->id === $contest->user_id, 403, 'You do not have permission to edit this contest.');

        return Inertia::render('contests/edit', [
            'contest' => $this->contestService->getAdminEditData($contest),
            'contestTypes' => \App\Enums\ContestType::selectOptions(),
        ]);
    }

    /**
     * Обновление конкурса.
     */
    public function update(\App\Http\Requests\ContestRequest $request, Contest $contest): RedirectResponse
    {
        abort_unless($this->user()->id === $contest->user_id, 403, 'You do not have permission to edit this contest.');

        $contest->update($request->validated());

        return to_route('admin.contests.show', $contest)->with('success', 'Contest updated successfully.');
    }

    /**
     * Удаление конкурса.
     */
    public function destroy(Contest $contest): RedirectResponse
    {
        abort_unless($this->user()->id === $contest->user_id, 403, 'You do not have permission to delete this contest.');

        $contest->delete();

        return to_route('admin.contests.index')->with('success', 'Contest deleted successfully.');
    }

    /**
     * Обновление статуса конкурса.
     */
    public function updateStatus(Request $request, Contest $contest): RedirectResponse
    {
        abort_unless($this->user()->id === $contest->user_id, 403, 'You do not have permission to edit this contest.');

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:draft,published,paused,closed'],
        ]);

        $contest->update(['status' => $validated['status']]);

        return back()->with('success', 'Contest status updated successfully.');
    }

    /**
     * Получить текущего авторизованного пользователя.
     */
    private function user(): \App\Models\User
    {
        $user = request()->user();

        if (! $user instanceof \App\Models\User) {
            abort(401, 'User not authenticated.');
        }

        return $user;
    }
}
