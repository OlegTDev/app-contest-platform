<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\QuizAnswer;
use App\Models\QuizEntry;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    /**
     * Display quiz entries for a contest (for organizer).
     */
    public function index(Request $request, Contest $contest): Response
    {
        $this->canManageContest($contest);

        $entries = $contest->quizEntries()
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'title' => $entry->title,
                'description' => $entry->description,
                'question' => $entry->question,
                'options' => $entry->options,
                'type' => $entry->type,
                'sort_order' => $entry->sort_order,
                'show_from' => $entry->show_from?->format('Y-m-d H:i'),
                'show_until' => $entry->show_until?->format('Y-m-d H:i'),
                'is_scheduled' => $entry->isScheduled(),
                'created_at' => $entry->created_at !== null ? $entry->created_at->format('Y-m-d H:i') : null,
            ]);

        return Inertia::render('quizzes/Index', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
                'type' => $contest->type->value,
            ],
            'entries' => $entries,
        ]);
    }

    /**
     * Show form for creating a new quiz entry.
     */
    public function create(Request $request, Contest $contest): Response
    {
        $this->canManageContest($contest);

        return Inertia::render('quizzes/Builder', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
                'type' => $contest->type->value,
            ],
        ]);
    }

    /**
     * Store a new quiz entry.
     */
    public function store(Request $request, Contest $contest): RedirectResponse
    {
        $this->canManageContest($contest);

        /** @var array{title: string, description: string|null, question: string, options: array<int, array{text: string, is_correct: bool}>, type: string, sort_order: int|null, show_from: string|null, show_until: string|null} $validated */
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

        // Build fields_data with option IDs
        $optionsWithIds = array_map(function ($opt, $index) {
            return [
                'id' => 'opt_'.($index + 1),
                'text' => $opt['text'],
                'is_correct' => $opt['is_correct'],
            ];
        }, $validated['options'], range(0, count($validated['options']) - 1));

        $fieldsData = [
            'question' => $validated['question'],
            'options' => $optionsWithIds,
            'type' => $validated['type'],
        ];

        $contest->quizEntries()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'fields_data' => $fieldsData,
            'sort_order' => $validated['sort_order'] ?? 0,
            'show_from' => $validated['show_from'] ? Carbon::parse($validated['show_from']) : null,
            'show_until' => $validated['show_until'] ? Carbon::parse($validated['show_until']) : null,
        ]);

        return redirect()->route('admin.entries.index', $contest)->with('success', 'Quiz card created successfully.');
    }

    /**
     * Show form for editing a quiz entry.
     */
    public function edit(Request $request, Contest $contest, QuizEntry $entry): Response
    {
        $this->canManageContest($contest);
        abort_unless($entry->contest_id === $contest->id, 404);

        return Inertia::render('quizzes/Builder', [
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
                'type' => $contest->type->value,
            ],
            'quiz' => [
                'id' => $entry->id,
                'title' => $entry->title,
                'description' => $entry->description,
                'question' => $entry->question,
                'options' => $entry->options,
                'type' => $entry->type,
            ],
            'initialQuestions' => [[
                'id' => 'q_1',
                'question' => $entry->question ?? '',
                'type' => $entry->type ?? 'single',
                'options' => ($entry->options ?? []),
                'correct_answer' => [],
            ]],
        ]);
    }

    /**
     * Update a quiz entry.
     */
    public function update(Request $request, Contest $contest, QuizEntry $entry): RedirectResponse
    {
        $this->canManageContest($contest);
        abort_unless($entry->contest_id === $contest->id, 404);

        /** @var array{title: string, description: string|null, question: string, options: array<int, array{text: string, is_correct: bool}>, type: string, sort_order: int|null, show_from: string|null, show_until: string|null} $validated */
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

        // Build fields_data with option IDs
        $optionsWithIds = array_map(function ($opt, $index) {
            return [
                'id' => 'opt_'.($index + 1),
                'text' => $opt['text'],
                'is_correct' => $opt['is_correct'],
            ];
        }, $validated['options'], range(0, count($validated['options']) - 1));

        $fieldsData = [
            'question' => $validated['question'],
            'options' => $optionsWithIds,
            'type' => $validated['type'],
        ];

        $entry->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'fields_data' => $fieldsData,
            'sort_order' => $validated['sort_order'] ?? $entry->sort_order,
            'show_from' => $validated['show_from'] ? Carbon::parse($validated['show_from']) : $entry->show_from,
            'show_until' => $validated['show_until'] ? Carbon::parse($validated['show_until']) : $entry->show_until,
        ]);

        return redirect()->route('admin.entries.index', $contest)->with('success', 'Quiz card updated successfully.');
    }

    /**
     * Delete a quiz entry.
     */
    public function destroy(Request $request, Contest $contest, QuizEntry $entry): RedirectResponse
    {
        $this->canManageContest($contest);
        abort_unless($entry->contest_id === $contest->id, 404);

        $entry->delete();

        return redirect()->route('admin.entries.index', $contest)->with('success', 'Quiz card deleted successfully.');
    }

    /**
     * Show quiz entry for answering (for participants).
     */
    public function take(Request $request, Contest $contest, QuizEntry $entry): Response
    {
        abort_unless($contest->isActive(), 403);
        abort_unless($entry->contest_id === $contest->id, 404);
        abort_unless($entry->isVisible(), 403);

        $user = $this->user();

        $existingAnswer = QuizAnswer::where('quiz_entry_id', $entry->id)
            ->where('user_id', $user->id)
            ->first();

        return Inertia::render('quizzes/Take', [
            'quiz' => [
                'id' => $entry->id,
                'title' => $entry->title,
                'description' => $entry->description,
                'question' => $entry->question,
                'options' => $entry->options,
                'type' => $entry->type,
            ],
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
            ],
            'alreadyCompleted' => $existingAnswer !== null,
            'existingAnswer' => $existingAnswer?->selected_options,
        ]);
    }

    /**
     * Submit answer for a quiz entry.
     */
    public function submit(Request $request, Contest $contest, QuizEntry $entry): RedirectResponse
    {
        abort_unless($contest->isActive(), 403);
        abort_unless($entry->contest_id === $contest->id, 404);
        abort_unless($entry->isVisible(), 403);

        $user = $this->user();

        /** @var array{selected_options: array<int, string>} $validated */
        $validated = $request->validate([
            'selected_options' => ['required', 'array'],
            'selected_options.*' => ['string', 'max:255'],
        ]);

        $selectedOptions = $validated['selected_options'];

        $isCorrect = $this->checkAnswer($entry, $selectedOptions);

        DB::transaction(function () use ($entry, $user, $selectedOptions, $isCorrect): void {
            // Use create instead of updateOrCreate to prevent answer changes
            QuizAnswer::create([
                'quiz_entry_id' => $entry->id,
                'user_id' => $user->id,
                'selected_options' => $selectedOptions,
                'is_correct' => $isCorrect,
                'answered_at' => now(),
            ]);
        });

        return redirect()->route('quizzes.result', [$contest, $entry]);
    }

    /**
     * Show quiz result.
     */
    public function result(Request $request, Contest $contest, QuizEntry $entry): Response|RedirectResponse
    {
        abort_unless($entry->contest_id === $contest->id, 404);

        $user = $this->user();

        $answer = QuizAnswer::where('quiz_entry_id', $entry->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $answer) {
            return redirect()->route('quizzes.take', [$contest, $entry]);
        }

        return Inertia::render('quizzes/Result', [
            'quiz' => [
                'id' => $entry->id,
                'title' => $entry->title,
                'description' => $entry->description,
                'question' => $entry->question,
                'options' => $entry->options,
                'type' => $entry->type,
            ],
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
            ],
            'answer' => [
                'is_correct' => $answer->is_correct,
                'selected_options' => $answer->selected_options,
                'answered_at' => $answer->answered_at?->format('Y-m-d H:i'),
            ],
        ]);
    }

    /**
     * Check if the answer is correct.
     *
     * @param  array<int, string>  $selectedOptions
     */
    protected function checkAnswer(QuizEntry $entry, array $selectedOptions): bool
    {
        $options = $entry->options;
        if ($options === null || empty($options)) {
            return false;
        }

        $correctOptionIds = array_filter($options, fn ($opt) => $opt['is_correct'] === true);
        $correctIds = array_map(fn ($opt) => $opt['id'], $correctOptionIds);
        $expectedCount = count($correctIds);
        $selectedCount = count($selectedOptions);

        // For single-answer quizzes, counts must match
        if ($entry->type === 'single' && $expectedCount !== $selectedCount) {
            return false;
        }

        // All selected must be correct and all correct must be selected
        return array_diff($selectedOptions, $correctIds) === []
            && array_diff($correctIds, $selectedOptions) === [];
    }

    /**
     * Get leaderboard for quiz entries.
     */
    public function leaderboard(Request $request, Contest $contest): JsonResponse
    {
        abort_unless($contest->isActive(), 403);

        $correctAnswers = QuizAnswer::whereHas('quizEntry', function ($query) use ($contest) {
            $query->where('contest_id', $contest->id);
        })
            ->where('is_correct', true)
            ->select('user_id')
            ->distinct()
            ->get()
            ->pluck('user_id')
            ->toArray();

        $leaderboard = [];
        foreach ($correctAnswers as $userId) {
            $count = QuizAnswer::where('is_correct', true)
                ->whereHas('quizEntry', fn ($q) => $q->where('contest_id', $contest->id))
                ->where('user_id', $userId)
                ->count();

            $leaderboard[] = [
                'user_id' => $userId,
                'correct_count' => $count,
            ];
        }

        usort($leaderboard, fn ($a, $b) => $b['correct_count'] <=> $a['correct_count']);

        return response()->json(['leaderboard' => $leaderboard]);
    }
}
