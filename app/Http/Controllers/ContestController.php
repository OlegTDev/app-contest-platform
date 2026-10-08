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
    /**
     * Публичная страница — только активные конкурсы
     */
    public function publicIndex(): Response
    {
        $contests = Contest::where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('start_at')
                      ->orWhere('start_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_at')
                      ->orWhere('end_at', '>=', now());
            })
            ->with(['quizEntries', 'entries'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($contest) => [
                'id' => $contest->id,
                'title' => $contest->title,
                'type' => $contest->type->value,
                'description' => $contest->description,
                'start_at' => $contest->start_at?->format('Y-m-d H:i'),
                'end_at' => $contest->end_at?->format('Y-m-d H:i'),
                'quiz_entry_count' => $contest->quizEntries->count(),
                'entry_count' => $contest->entries->count(),
                'created_at' => $contest->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('contests/PublicIndex', [
            'contests' => $contests,
        ]);
    }

    /**
     * Публичная страница конкурса — детали и активности
     */
    public function publicShow(Request $request, Contest $contest): Response
    {
        // Only show published contests that are currently active
        abort_unless($contest->status === 'published', 404);

        $contest->load(['quizEntries' => function ($query) {
            $query->where(function ($q) {
                $q->whereNull('show_from')
                  ->orWhere('show_from', '<=', now());
            })->where(function ($q) {
                $q->whereNull('show_until')
                  ->orWhere('show_until', '>=', now());
            })->orderBy('sort_order');
        }, 'entries' => function ($query) {
            $query->orderBy('id');
        }, 'entries.media']);

        // Check if contest time window is active
        $now = now();
        $isTimeActive = true;

        if ($contest->start_at && $now->lt($contest->start_at)) {
            $isTimeActive = false;
        }

        if ($contest->end_at && $now->gt($contest->end_at)) {
            $isTimeActive = false;
        }

        $user = $request->user();
        $userVotes = $user
            ? $user->votes()->where('contest_id', $contest->id)->pluck('entry_id')->toArray()
            : [];

        $activities = [];

        if ($contest->type->value === 'quiz') {
            $activities = $contest->quizEntries->map(fn ($entry) => [
                'id' => $entry->id,
                'type' => 'quiz',
                'title' => $entry->title,
                'description' => $entry->description,
                'is_visible' => $entry->isVisible(),
                'created_at' => $entry->created_at->format('Y-m-d H:i'),
            ]);
        } else {
            $activities = $contest->entries->map(fn ($entry) => [
                'id' => $entry->id,
                'type' => 'voting',
                'title' => $entry->title,
                'description' => $entry->description,
                'author_name' => $entry->author_name,
                'author_department' => $entry->author_department,
                'votes_count' => $entry->votes_count,
                'is_voted' => in_array($entry->id, $userVotes),
                'fields_data' => $entry->fields_data,
                'media' => $entry->media->map(fn ($m) => [
                    'id' => $m->id,
                    'file_name' => $m->file_name,
                    'file_url' => $m->file_url,
                    'file_type' => $m->file_type,
                    'file_extension' => $m->file_extension,
                    'file_size' => $m->file_size,
                    'is_main' => $m->is_main,
                    'created_at' => $m->created_at->format('Y-m-d H:i'),
                ]),
                'created_at' => $entry->created_at->format('Y-m-d H:i'),
            ]);
        }

        $leaderboard = [];
        if ($contest->type->value === 'voting') {
            $leaderboard = $contest->entries()
                ->whereNotNull('votes_count')
                ->orderByDesc('votes_count')
                ->limit(50)
                ->get()
                ->map(fn ($entry) => [
                    'id' => $entry->id,
                    'title' => $entry->title,
                    'votes_count' => $entry->votes_count,
                    'author' => $entry->author_name ? [
                        'name' => $entry->author_name,
                    ] : null,
                ]);
        }

        return Inertia::render('contests/PublicShow', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
                'type' => $contest->type->value,
                'status' => $contest->status,
                'description' => $contest->description,
                'start_at' => $contest->start_at?->format('Y-m-d H:i'),
                'end_at' => $contest->end_at?->format('Y-m-d H:i'),
                'is_active' => $contest->isActive(),
                'is_time_active' => $isTimeActive,
                'activities' => $activities,
                'created_at' => $contest->created_at->format('Y-m-d H:i'),
            ],
            'leaderboard' => $leaderboard,
        ]);
    }

    /**
     * Админка — все конкурсы
     */
    public function index(Request $request): Response
    {
        $query = Contest::with(['quizEntries', 'entries'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('title', 'ilike', '%' . $request->search . '%');
        }

        $contests = $query->paginate(15);

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

        $activities = [];

        if ($contest->type->value === 'quiz') {
            $activities = $contest->quizEntries->map(fn ($entry) => [
                'id' => $entry->id,
                'type' => 'quiz',
                'title' => $entry->title,
                'description' => $entry->description,
                'is_scheduled' => $entry->isScheduled(),
                'is_visible' => $entry->isVisible(),
                'created_at' => $entry->created_at->format('Y-m-d H:i'),
            ]);
        } else {
            $activities = $contest->entries->map(fn ($entry) => [
                'id' => $entry->id,
                'type' => 'voting',
                'title' => $entry->title,
                'description' => $entry->description,
                'author_name' => $entry->author_name,
                'author_department' => $entry->author_department,
                'votes_count' => $entry->votes_count,
                'created_at' => $entry->created_at->format('Y-m-d H:i'),
            ]);
        }

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
                'activities' => $activities,
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
