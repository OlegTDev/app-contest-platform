<?php

namespace App\Http\Controllers;

use App\Enums\ContestType;
use App\Http\Requests\ContestRequest;
use App\Models\Contest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContestController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Contest::with(['quizEntries', 'entries'])
            ->orderByDesc('created_at');

        // Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Фильтр по типу
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Поиск по названию
        if ($request->filled('search')) {
            $query->where('title', 'ilike', '%' . $request->search . '%');
        }

        $contests = $query->paginate(15);

        // Transform items while keeping paginator structure
        $contests->transform(fn ($contest) => [
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
                'name' => $contest->author?->name ?? 'Unknown',
            ],
            'is_owner' => $request->user()->id === $contest->user_id,
            'created_at' => $contest->created_at->format('Y-m-d H:i'),
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

    public function create(): Response
    {
        $contestTypes = ContestType::selectOptions();
        return Inertia::render('contests/create', [
            'contestTypes' => $contestTypes,
        ]);
    }

    public function store(ContestRequest $request): RedirectResponse
    {
        Contest::create($request->validated());
        return to_route('contest.index')->with('success', 'Contest created successfully.');
    }

    public function show(Request $request, Contest $contest): Response
    {
        $contest->load(['quizEntries' => function ($query) {
            $query->orderBy('sort_order');
        }, 'entries' => function ($query) {
            $query->orderBy('id');
        }]);

        return Inertia::render('contests/show', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
                'type' => $contest->type->value,
                'status' => $contest->status,
                'description' => $contest->description,
                'start_at' => $contest->start_at?->format('Y-m-d H:i'),
                'end_at' => $contest->end_at?->format('Y-m-d H:i'),
                'is_active' => $contest->isActive(),
                'is_owner' => $request->user()->id === $contest->user_id,
                'quizEntries' => $contest->quizEntries->map(fn ($entry) => [
                    'id' => $entry->id,
                    'title' => $entry->title,
                    'description' => $entry->description,
                    'is_scheduled' => $entry->isScheduled(),
                    'created_at' => $entry->created_at->format('Y-m-d H:i'),
                ]),
                'entries' => $contest->entries->map(fn ($entry) => [
                    'id' => $entry->id,
                    'title' => $entry->title,
                    'description' => $entry->description,
                    'author_name' => $entry->author_name,
                    'author_department' => $entry->author_department,
                    'votes_count' => $entry->votes_count,
                    'created_at' => $entry->created_at->format('Y-m-d H:i'),
                ]),
                'created_at' => $contest->created_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    public function edit(Request $request, Contest $contest): Response
    {
        abort_unless($request->user()->id === $contest->user_id, 403, 'You do not have permission to edit this contest.');

        $contestTypes = ContestType::selectOptions();

        return Inertia::render('contests/edit', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
                'type' => $contest->type->value,
                'status' => $contest->status,
                'description' => $contest->description,
                'start_at' => $contest->start_at?->format('Y-m-d H:i'),
                'end_at' => $contest->end_at?->format('Y-m-d H:i'),
                'project_schema' => $contest->project_schema ?? [],
            ],
            'contestTypes' => $contestTypes,
        ]);
    }

    public function update(ContestRequest $request, Contest $contest): RedirectResponse
    {
        abort_unless($request->user()->id === $contest->user_id, 403, 'You do not have permission to edit this contest.');

        $validated = $request->validated();
        $contest->update($validated);

        return to_route('contest.show', $contest)->with('success', 'Contest updated successfully.');
    }

    public function destroy(Request $request, Contest $contest): RedirectResponse
    {
        abort_unless($request->user()->id === $contest->user_id, 403, 'You do not have permission to delete this contest.');

        $contest->delete();
        return to_route('contest.index')->with('success', 'Contest deleted successfully.');
    }

    public function updateStatus(Request $request, Contest $contest): RedirectResponse
    {
        abort_unless($request->user()->id === $contest->user_id, 403, 'You do not have permission to edit this contest.');

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:draft,published,paused,closed'],
        ]);

        $contest->update(['status' => $validated['status']]);

        return back()->with('success', 'Contest status updated successfully.');
    }
}
