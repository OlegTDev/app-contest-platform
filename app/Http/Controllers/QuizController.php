<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\QuizAnswer;
use App\Models\QuizEntry;
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
        abort_unless($request->user()->id === $contest->user_id, 403);

        $entries = $contest->quizEntries()
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
                'created_at' => $entry->created_at->format('Y-m-d H:i'),
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
     * Show quiz entry for answering (for participants).
     */
    public function take(Request $request, Contest $contest, QuizEntry $entry): Response
    {
        abort_unless($contest->isActive(), 403);
        abort_unless($entry->contest_id === $contest->id, 404);
        abort_unless($entry->isVisible(), 403);

        $user = $request->user();

        $existingAnswer = QuizAnswer::where('quiz_entry_id', $entry->id)
            ->where('user_id', $user->id)
            ->first();

        return Inertia::render('quizzes/Take', [
            'quiz' => [
                'id' => $entry->id,
                'title' => $entry->title,
                'description' => $entry->description,
                'fields_data' => $entry->fields_data,
            ],
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
            ],
            'alreadyCompleted' => $existingAnswer !== null,
            'existingAnswer' => $existingAnswer?->answer,
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

        $user = $request->user();

        $validated = $request->validate([
            'answer' => ['required', 'string', 'max:255'],
        ]);

        // Here you would compare the answer with the correct answer
        // For now, we just store it and mark as correct/incorrect based on logic
        $isCorrect = $this->checkAnswer($entry, $validated['answer']);

        DB::transaction(function () use ($entry, $user, $validated, $isCorrect) {
            QuizAnswer::updateOrCreate(
                [
                    'quiz_entry_id' => $entry->id,
                    'user_id' => $user->id,
                ],
                [
                    'answer' => $validated['answer'],
                    'is_correct' => $isCorrect,
                    'answered_at' => now(),
                ]
            );
        });

        return redirect()->route('quizzes.result', [$contest, $entry]);
    }

    /**
     * Show quiz result.
     */
    public function result(Request $request, Contest $contest, QuizEntry $entry): Response
    {
        abort_unless($entry->contest_id === $contest->id, 404);

        $user = $request->user();

        $answer = QuizAnswer::where('quiz_entry_id', $entry->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$answer) {
            return redirect()->route('quizzes.take', [$contest, $entry]);
        }

        return Inertia::render('quizzes/Result', [
            'quiz' => [
                'id' => $entry->id,
                'title' => $entry->title,
                'description' => $entry->description,
                'fields_data' => $entry->fields_data,
            ],
            'contest' => [
                'id' => $contest->id,
                'title' => $contest->title,
            ],
            'answer' => [
                'is_correct' => $answer->is_correct,
                'user_answer' => $answer->answer,
                'answered_at' => $answer->answered_at?->format('Y-m-d H:i'),
            ],
        ]);
    }

    /**
     * Check if the answer is correct.
     */
    protected function checkAnswer(QuizEntry $entry, string $answer): bool
    {
        // TODO: Implement actual answer checking logic
        // This depends on how you store correct answers in fields_data
        // For now, return true as placeholder
        return true;
    }

    /**
     * Get leaderboard for quiz entries.
     */
    public function leaderboard(Request $request, Contest $contest): JsonResponse
    {
        abort_unless($contest->isActive(), 403);

        $leaderboard = QuizAnswer::whereHas('quizEntry', function ($query) use ($contest) {
            $query->where('contest_id', $contest->id);
        })
            ->with(['user:id,name', 'quizEntry:title'])
            ->where('is_correct', true)
            ->select('user_id', 'quiz_entry_id')
            ->groupBy('user_id', 'quiz_entry_id')
            ->count()
            ->groupBy('user_id')
            ->map(fn ($count) => [
                'user_id' => $count->first()->user_id,
                'correct_count' => $count->count(),
            ])
            ->sortByDesc('correct_count')
            ->values();

        return response()->json(['leaderboard' => $leaderboard]);
    }
}
