<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\ContestEntry;
use App\Models\QuizEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EntryController extends Controller
{
    /**
     * Display entries for a contest (for organizer).
     */
    public function index(Request $request, Contest $contest): Response
    {
        abort_unless($request->user()->id === $contest->user_id, 403);

        if ($contest->type->value === 'voting') {
            $entries = $contest->entries()
                ->with('media')
                ->orderBy('id')
                ->get()
                ->map(fn ($entry) => [
                    'id' => $entry->id,
                    'title' => $entry->title,
                    'description' => $entry->description,
                    'author_name' => $entry->author_name,
                    'author_department' => $entry->author_department,
                    'fields_data' => $entry->fields_data,
                    'votes_count' => $entry->votes_count,
                    'media' => $entry->media->map(fn ($m) => [
                        'id' => $m->id,
                        'file_name' => $m->file_name,
                        'file_url' => $m->file_url,
                        'file_type' => $m->file_type,
                        'file_extension' => $m->file_extension,
                        'file_size' => $m->file_size,
                        'created_at' => $m->created_at->format('Y-m-d H:i'),
                    ]),
                    'created_at' => $entry->created_at->format('Y-m-d H:i'),
                ]);
        } else {
            $entries = $contest->quizEntries()
                ->with('media')
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($entry) => [
                    'id' => $entry->id,
                    'title' => $entry->title,
                    'description' => $entry->description,
                    'fields_data' => $entry->fields_data,
                    'sort_order' => $entry->sort_order,
                    'show_from' => $entry->show_from?->format('Y-m-d H:i'),
                    'show_until' => $entry->show_until?->format('Y-m-d H:i'),
                    'is_scheduled' => $entry->isScheduled(),
                    'media' => $entry->media->map(fn ($m) => [
                        'id' => $m->id,
                        'file_name' => $m->file_name,
                        'file_url' => $m->file_url,
                        'file_type' => $m->file_type,
                        'file_extension' => $m->file_extension,
                        'file_size' => $m->file_size,
                        'created_at' => $m->created_at->format('Y-m-d H:i'),
                    ]),
                    'created_at' => $entry->created_at->format('Y-m-d H:i'),
                ]);
        }

        return Inertia::render('entries/Index', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
                'type' => $contest->type->value,
            ],
            'entries' => $entries,
        ]);
    }

    /**
     * Show form for creating a new entry.
     */
    public function create(Request $request, Contest $contest): Response
    {
        abort_unless($request->user()->id === $contest->user_id, 403);

        return Inertia::render('entries/Create', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
                'type' => $contest->type->value,
            ],
        ]);
    }

    /**
     * Store a newly created entry.
     */
    public function store(Request $request, Contest $contest): RedirectResponse
    {
        abort_unless($request->user()->id === $contest->user_id, 403);

        if ($contest->type->value === 'voting') {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
                'author_name' => ['nullable', 'string', 'max:255'],
                'author_department' => ['nullable', 'string', 'max:255'],
                'fields_data' => ['nullable', 'array'],
            ]);

            $contest->entries()->create([...$validated, 'user_id' => $request->user()->id]);
        } else {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
                'fields_data' => ['nullable', 'array'],
                'sort_order' => ['nullable', 'integer'],
                'show_from' => ['nullable', 'date_format:Y-m-d H:i:s'],
                'show_until' => ['nullable', 'date_format:Y-m-d H:i:s'],
            ]);

            $contest->quizEntries()->create($validated);
        }

        return redirect()->route('entries.index', $contest)->with('success', 'Entry created successfully.');
    }

    /**
     * Show form for editing an entry.
     */
    public function edit(Request $request, Contest $contest, ContestEntry|QuizEntry $entry): Response
    {
        abort_unless($request->user()->id === $contest->user_id, 403);
        abort_unless($entry->contest_id === $contest->id, 404);

        if ($contest->type->value === 'voting') {
            $entry = $contest->entries()->findOrFail($entry->id);

            return Inertia::render('entries/Edit', [
                'contest' => [
                    'id' => $contest->id,
                    'title' => $contest->title,
                    'type' => $contest->type->value,
                ],
                'entry' => [
                    'id' => $entry->id,
                    'title' => $entry->title,
                    'description' => $entry->description,
                    'author_name' => $entry->author_name,
                    'author_department' => $entry->author_department,
                    'fields_data' => $entry->fields_data,
                ],
            ]);
        }

        $entry = $contest->quizEntries()->findOrFail($entry->id);

        return Inertia::render('entries/Edit', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
                'type' => $contest->type->value,
            ],
            'entry' => [
                'id' => $entry->id,
                'title' => $entry->title,
                'description' => $entry->description,
                'fields_data' => $entry->fields_data,
                'sort_order' => $entry->sort_order,
                'show_from' => $entry->show_from?->format('Y-m-d H:i'),
                'show_until' => $entry->show_until?->format('Y-m-d H:i'),
            ],
        ]);
    }

    /**
     * Update an entry.
     */
    public function update(Request $request, Contest $contest, ContestEntry|QuizEntry $entry): RedirectResponse
    {
        abort_unless($request->user()->id === $contest->user_id, 403);
        abort_unless($entry->contest_id === $contest->id, 404);

        if ($contest->type->value === 'voting') {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
                'author_name' => ['nullable', 'string', 'max:255'],
                'author_department' => ['nullable', 'string', 'max:255'],
                'fields_data' => ['nullable', 'array'],
            ]);

            $contest->entries()->where('id', $entry->id)->update($validated);
        } else {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
                'fields_data' => ['nullable', 'array'],
                'sort_order' => ['nullable', 'integer'],
                'show_from' => ['nullable', 'date_format:Y-m-d H:i:s'],
                'show_until' => ['nullable', 'date_format:Y-m-d H:i:s'],
            ]);

            $contest->quizEntries()->where('id', $entry->id)->update($validated);
        }

        return redirect()->route('entries.index', $contest)->with('success', 'Entry updated successfully.');
    }

    /**
     * Delete an entry.
     */
    public function destroy(Request $request, Contest $contest, ContestEntry|QuizEntry $entry): RedirectResponse
    {
        abort_unless($request->user()->id === $contest->user_id, 403);
        abort_unless($entry->contest_id === $contest->id, 404);

        if ($contest->type->value === 'voting') {
            $contest->entries()->where('id', $entry->id)->delete();
        } else {
            $contest->quizEntries()->where('id', $entry->id)->delete();
        }

        return redirect()->route('entries.index', $contest)->with('success', 'Entry deleted successfully.');
    }

    /**
     * Display entries for public voting (for participants).
     */
    public function publicIndex(Request $request, Contest $contest): Response
    {
        abort_unless($contest->isActive(), 403);

        $user = $request->user();

        if ($contest->type->value === 'voting') {
            $query = $contest->entries();

            $entries = $query->get()->filter(function ($entry) {
                return $entry->isVisible();
            })->sortBy('id')->values();

            $userVotes = $user->votes()
                ->where('contest_id', $contest->id)
                ->pluck('entry_id')
                ->toArray();

            return Inertia::render('entries/PublicIndex', [
                'contest' => [
                    'id' => $contest->id,
                    'title' => $contest->title,
                    'type' => $contest->type->value,
                ],
                'entries' => $entries->map(fn ($entry) => [
                    'id' => $entry->id,
                    'title' => $entry->title,
                    'description' => $entry->description,
                    'author_name' => $entry->author_name,
                    'author_department' => $entry->author_department,
                    'fields_data' => $entry->fields_data,
                    'votes_count' => $entry->votes_count,
                    'is_voted' => in_array($entry->id, $userVotes),
                    'media' => $entry->media->map(fn ($m) => [
                        'id' => $m->id,
                        'file_name' => $m->file_name,
                        'file_url' => $m->file_url,
                        'file_type' => $m->file_type,
                        'file_extension' => $m->file_extension,
                        'file_size' => $m->file_size,
                        'created_at' => $m->created_at->format('Y-m-d H:i'),
                    ]),
                ]),
            ]);
        }

        // For quiz contests
        $query = $contest->quizEntries();

        $entries = $query->get()->filter(function ($entry) {
            return $entry->isVisible();
        })->sortBy('sort_order')->values();

        return Inertia::render('entries/PublicIndex', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
                'type' => $contest->type->value,
            ],
            'entries' => $entries->map(fn ($entry) => [
                'id' => $entry->id,
                'title' => $entry->title,
                'description' => $entry->description,
                'fields_data' => $entry->fields_data,
                'is_scheduled' => $entry->isScheduled(),
                'media' => $entry->media->map(fn ($m) => [
                    'id' => $m->id,
                    'file_name' => $m->file_name,
                    'file_url' => $m->file_url,
                    'file_type' => $m->file_type,
                    'file_extension' => $m->file_extension,
                    'file_size' => $m->file_size,
                    'created_at' => $m->created_at->format('Y-m-d H:i'),
                ]),
            ]),
        ]);
    }
}
