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
            'contestTypes' => ContestType::selectOptions(),
        ]);
    }

    /**
     * Сохранение нового конкурса.
     */
    public function store(ContestRequest $request): RedirectResponse
    {
        Contest::create($request->validated());

        return to_route('admin.contests.index')->with('success', 'Contest created successfully.');
    }

    /**
     * Админка — страница конкурса.
     */
    public function show(Contest $contest): Response
    {
        $data = $this->contestService->getAdminShowData($contest, $this->user()->id);

        return Inertia::render('contests/show', [
            'contest' => $data,
        ]);
    }

    /**
     * Форма редактирования конкурса.
     */
    public function edit(Contest $contest): Response
    {
        $this->canManageContest($contest);

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
        $this->canManageContest($contest);

        $contest->update($request->validated());

        return to_route('admin.contests.show', $contest)->with('success', 'Contest updated successfully.');
    }

    /**
     * Удаление конкурса.
     */
    public function destroy(Contest $contest): RedirectResponse
    {
        $this->canManageContest($contest);

        $contest->delete();

        return to_route('admin.contests.index')->with('success', 'Contest deleted successfully.');
    }

    /**
     * Обновление статуса конкурса.
     */
    public function updateStatus(Request $request, Contest $contest): RedirectResponse
    {
        $this->canManageContest($contest);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:draft,published,paused,closed'],
        ]);

        $contest->update(['status' => $validated['status']]);

        return back()->with('success', 'Contest status updated successfully.');
    }
}
