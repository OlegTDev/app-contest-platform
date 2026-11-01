<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\BuildQuizFieldsDataAction;
use App\Actions\SubmitQuizAnswerAction;
use App\Http\Requests\StoreQuizEntryRequest;
use App\Http\Requests\SubmitQuizAnswerRequest;
use App\Http\Requests\UpdateQuizEntryRequest;
use App\Models\Contest;
use App\Models\QuizAnswer;
use App\Models\QuizEntry;
use App\Services\UserContestActivityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    public function __construct(
        private readonly UserContestActivityService $userContestActivity,
    ) {}

    /**
     * Display quiz entries for a contest (for organizer).
     */
    public function index(Contest $contest): Response
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
    public function create(Contest $contest): Response
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
    public function store(
        StoreQuizEntryRequest $request,
        Contest $contest,
        BuildQuizFieldsDataAction $buildFieldsData,
    ): RedirectResponse {
        $this->canManageContest($contest);

        $validated = $request->validated();

        $fieldsData = $buildFieldsData->execute(
            $validated['options'],
            $validated['question'],
            $validated['type'],
        );

        $contest->quizEntries()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'fields_data' => $fieldsData,
            'sort_order' => $validated['sort_order'] ?? 0,
            'show_from' => ($validated['show_from'] ?? null) ? Carbon::parse($validated['show_from']) : null,
            'show_until' => ($validated['show_until'] ?? null) ? Carbon::parse($validated['show_until']) : null,
        ]);

        return redirect()->route('admin.contests.show', $contest)
            ->with('success', 'Quiz card created successfully.');
    }

    /**
     * Show form for editing a quiz entry.
     */
    public function edit(Contest $contest, QuizEntry $entry): Response
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
        ]);
    }

    /**
     * Update a quiz entry.
     */
    public function update(
        UpdateQuizEntryRequest $request,
        Contest $contest,
        QuizEntry $entry,
        BuildQuizFieldsDataAction $buildFieldsData,
    ): RedirectResponse {
        $this->canManageContest($contest);
        abort_unless($entry->contest_id === $contest->id, 404);

        $validated = $request->validated();

        $fieldsData = $buildFieldsData->execute(
            $validated['options'],
            $validated['question'],
            $validated['type'],
        );

        $entry->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'fields_data' => $fieldsData,
            'sort_order' => $validated['sort_order'] ?? $entry->sort_order,
            'show_from' => ($validated['show_from'] ?? null)
                ? Carbon::parse($validated['show_from'])
                : $entry->show_from,
            'show_until' => ($validated['show_until'] ?? null)
                ? Carbon::parse($validated['show_until'])
                : $entry->show_until,
        ]);

        return redirect()->route('admin.contests.show', $contest)
            ->with('success', 'Quiz card updated successfully.');
    }

    /**
     * Delete a quiz entry.
     */
    public function destroy(Contest $contest, QuizEntry $entry): RedirectResponse
    {
        $this->canManageContest($contest);
        abort_unless($entry->contest_id === $contest->id, 404);

        $entry->delete();

        return redirect()->route('admin.contests.show', $contest)
            ->with('success', 'Quiz card deleted successfully.');
    }

    /**
     * Show quiz entry for answering (for participants).
     */
    public function take(Contest $contest, QuizEntry $entry): Response
    {
        if (! in_array($contest->status, ['published', 'archived', 'closed', 'paused'], strict: true)) {
            abort(404);
        }
        $this->ensureEntryBelongsToContest($contest, $entry);
        abort_unless($entry->isVisible(), 403);

        $user = $this->user();

        $existingAnswer = $this->userContestActivity->getUserAnswerForQuizEntry($user, $entry);

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
    public function submit(
        SubmitQuizAnswerAction $submitAction,
        Contest $contest,
        QuizEntry $entry,
        SubmitQuizAnswerRequest $request,
    ): RedirectResponse {
        if (! in_array($contest->status, ['published', 'archived', 'closed', 'paused'], strict: true)) {
            abort(404);
        }
        $this->ensureEntryBelongsToContest($contest, $entry);
        abort_unless($entry->isVisible(), 403);

        $user = $this->user();

        $validated = $request->validated();

        $submitAction->execute($entry, $user->id, $validated['selected_options']);

        return redirect()->route('quizzes.result', [$contest, $entry]);
    }

    /**
     * Show quiz result.
     */
    public function result(Contest $contest, QuizEntry $entry): Response|RedirectResponse
    {
        $this->ensureEntryBelongsToContest($contest, $entry);

        $user = $this->user();

        $answer = $this->userContestActivity->getUserAnswerForQuizEntry($user, $entry);

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
     * Get leaderboard for quiz entries.
     * Uses a single GROUP BY query instead of N+1 per user.
     */
    public function leaderboard(Contest $contest): JsonResponse
    {
        if (! in_array($contest->status, ['published', 'archived', 'closed', 'paused'], strict: true)) {
            abort(404);
        }

        $leaderboard = QuizAnswer::whereHas('quizEntry', function ($query) use ($contest) {
            $query->where('contest_id', $contest->id);
        })
            ->where('is_correct', true)
            ->select('user_id')
            ->selectRaw('count(*) as correct_count')
            ->groupBy('user_id')
            ->orderByDesc('correct_count')
            ->limit(50)
            ->get()
            ->map(fn ($row) => [
                'user_id' => $row->user_id,
                'correct_count' => (int) $row->correct_count,
            ]);

        return response()->json(['leaderboard' => $leaderboard]);
    }

    private function ensureEntryBelongsToContest(Contest $contest, QuizEntry $entry): void
    {
        abort_unless($entry->contest_id === $contest->id, 404);
    }
}
