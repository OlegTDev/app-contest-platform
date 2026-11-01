<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EntryController extends Controller
{
    /**
     * Show form for creating a new entry.
     */
    public function create(Request $request, Contest $contest): Response
    {
        $this->canManageContest($contest);

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
        $this->canManageContest($contest);

        if ($contest->type->value === 'voting') {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
                'author_name' => ['nullable', 'string', 'max:255'],
                'author_department' => ['nullable', 'string', 'max:255'],
                'fields_data' => ['nullable', 'array'],
            ]);
            $contest->entries()->create([...$validated, 'user_id' => $this->user()->id]);
        } else {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
                'question' => ['required', 'string', 'max:2000'],
                'options' => ['required', 'array', 'min:2'],
                'options.*.text' => ['required', 'string', 'max:500'],
                'options.*.is_correct' => ['required', 'boolean'],
                'type' => ['required', 'in:single,multiple'],
                'sort_order' => ['nullable', 'integer'],
                'show_from' => ['nullable', 'date_format:Y-m-d H:i:s'],
                'show_until' => ['nullable', 'date_format:Y-m-d H:i:s'],
            ]);

            $fieldsData = [
                'question' => $validated['question'],
                'options' => array_map(function ($opt, $index) {
                    return [
                        'id' => 'opt_'.($index + 1),
                        'text' => $opt['text'],
                        'is_correct' => $opt['is_correct'],
                    ];
                }, $validated['options'], range(0, count($validated['options']) - 1)),
                'type' => $validated['type'],
            ];

            $contest->quizEntries()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'fields_data' => $fieldsData,
                'sort_order' => $validated['sort_order'] ?? 0,
                'show_from' => ($validated['show_from'] ?? null) ? Carbon::parse($validated['show_from']) : null,
                'show_until' => ($validated['show_until'] ?? null) ? Carbon::parse($validated['show_until']) : null,
            ]);
        }

        return redirect()->route('admin.contests.show', $contest)
            ->with('success', 'Entry created successfully.');
    }

    /**
     * Show form for editing an entry.
     */
    public function edit(Request $request, Contest $contest, string $entry): Response
    {
        $this->canManageContest($contest);

        if ($contest->type->value === 'voting') {
            $entryModel = $contest->entries()->findOrFail($entry);

            return Inertia::render('entries/Edit', [
                'contest' => [
                    'id' => $contest->id,
                    'title' => $contest->title,
                    'type' => $contest->type->value,
                ],
                'entry' => [
                    'id' => $entryModel->id,
                    'title' => $entryModel->title,
                    'description' => $entryModel->description,
                    'author_name' => $entryModel->author_name,
                    'author_department' => $entryModel->author_department,
                    'fields_data' => $entryModel->fields_data,
                ],
            ]);
        }

        $entryModel = $contest->quizEntries()->findOrFail($entry);

        return Inertia::render('entries/Edit', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
                'type' => $contest->type->value,
            ],
            'entry' => [
                'id' => $entryModel->id,
                'title' => $entryModel->title,
                'description' => $entryModel->description,
                'question' => $entryModel->question,
                'options' => $entryModel->options,
                'type' => $entryModel->type,
                'fields_data' => $entryModel->fields_data,
                'sort_order' => $entryModel->sort_order,
                'show_from' => $entryModel->show_from?->format('Y-m-d H:i'),
                'show_until' => $entryModel->show_until?->format('Y-m-d H:i'),
            ],
        ]);
    }

    /**
     * Update an entry.
     */
    public function update(Request $request, Contest $contest, string $entry): RedirectResponse
    {
        $this->canManageContest($contest);

        if ($contest->type->value === 'voting') {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
                'author_name' => ['nullable', 'string', 'max:255'],
                'author_department' => ['nullable', 'string', 'max:255'],
                'fields_data' => ['nullable', 'array'],
            ]);
            $contest->entries()->where('id', $entry)->update($validated);
        } else {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
                'question' => ['required', 'string', 'max:2000'],
                'options' => ['required', 'array', 'min:2'],
                'options.*.text' => ['required', 'string', 'max:500'],
                'options.*.is_correct' => ['required', 'boolean'],
                'type' => ['required', 'in:single,multiple'],
                'sort_order' => ['nullable', 'integer'],
                'show_from' => ['nullable', 'date_format:Y-m-d H:i:s'],
                'show_until' => ['nullable', 'date_format:Y-m-d H:i:s'],
            ]);

            $fieldsData = [
                'question' => $validated['question'],
                'options' => array_map(function ($opt, $index) {
                    return [
                        'id' => 'opt_'.($index + 1),
                        'text' => $opt['text'],
                        'is_correct' => $opt['is_correct'],
                    ];
                }, $validated['options'], range(0, count($validated['options']) - 1)),
                'type' => $validated['type'] ?? 'single',
            ];

            $updateData = [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'fields_data' => $fieldsData,
            ];

            if (isset($validated['show_from']) && $validated['show_from'] !== '') {
                $updateData['show_from'] = Carbon::parse($validated['show_from']);
            }

            if (isset($validated['show_until']) && $validated['show_until'] !== '') {
                $updateData['show_until'] = Carbon::parse($validated['show_until']);
            }

            $contest->quizEntries()->where('id', $entry)->update($updateData);
        }

        return redirect()->route('admin.contests.show', $contest)
            ->with('success', 'Entry updated successfully.');
    }

    /**
     * Delete an entry.
     */
    public function destroy(Request $request, Contest $contest, string $entry): RedirectResponse
    {
        $this->canManageContest($contest);

        if ($contest->type->value === 'voting') {
            $contest->entries()->where('id', $entry)->delete();
        } else {
            $contest->quizEntries()->where('id', $entry)->delete();
        }

        return redirect()->route('admin.contests.show', $contest)
            ->with('success', 'Entry deleted successfully.');
    }
}
