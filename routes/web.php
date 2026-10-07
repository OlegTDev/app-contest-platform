<?php

use App\Http\Controllers\ContestController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Route;


// Public routes (no authentication required)
Route::get('/', [ContestController::class, 'publicIndex'])->name('contests.public');
Route::get('contests/{contest}', [ContestController::class, 'publicShow'])->name('contests.public.show');

// Public entry voting (no authentication required)
Route::prefix('contest/{contest}')->group(function () {
    Route::get('entries/public', [EntryController::class, 'publicIndex'])->name('entries.public');
    Route::post('entries/{entry}/vote', [VoteController::class, 'store'])->name('entries.vote');
    Route::delete('entries/{entry}/vote', [VoteController::class, 'destroy'])->name('entries.vote.destroy');
    Route::get('entries/leaderboard', [VoteController::class, 'leaderboard'])->name('entries.leaderboard');
});

Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('contest', ContestController::class);
    Route::patch('contest/{contest}/status', [ContestController::class, 'updateStatus'])->name('contest.status');

    // Media routes (nested under contest)
    Route::prefix('contest/{contest}')->group(function () {
        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

        // Entry media routes
        Route::get('entries/{entry}/media', [MediaController::class, 'entryIndex'])->name('entry.media.index');
        Route::post('entries/{entry}/media', [MediaController::class, 'entryStore'])->name('entry.media.store');
        Route::delete('entries/{entry}/media/{media}', [MediaController::class, 'entryDestroy'])->name('entry.media.destroy');
        Route::post('entries/{entry}/media/{media}/main', [MediaController::class, 'toggleMain'])->name('entry.media.toggleMain');
    });

    // Quiz routes (nested under contest)
    Route::prefix('contest/{contest}')->group(function () {
        Route::get('quizzes', [QuizController::class, 'index'])->name('quizzes.index');
        Route::get('quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
        Route::post('quizzes', [QuizController::class, 'store'])->name('quizzes.store');
        Route::get('quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
        Route::patch('quizzes/{quiz}', [QuizController::class, 'update'])->name('quizzes.update');
        Route::delete('quizzes/{quiz}', [QuizController::class, 'destroy'])->name('quizzes.destroy');
        Route::get('quizzes/{quiz}/take', [QuizController::class, 'take'])->name('quizzes.take');
        Route::post('quizzes/{quiz}/submit', [QuizController::class, 'submit'])->name('quizzes.submit');
        Route::get('quizzes/{quiz}/result', [QuizController::class, 'result'])->name('quizzes.result');
        Route::get('quizzes/{quiz}/leaderboard', [QuizController::class, 'leaderboard'])->name('quizzes.leaderboard');

        // Entry routes (for voting contests and quiz questions)
        Route::get('entries', [EntryController::class, 'index'])->name('entries.index');
        Route::get('entries/create', [EntryController::class, 'create'])->name('entries.create');
        Route::post('entries', [EntryController::class, 'store'])->name('entries.store');
        Route::get('entries/{entry}/edit', [EntryController::class, 'edit'])->name('entries.edit');
        Route::patch('entries/{entry}', [EntryController::class, 'update'])->name('entries.update');
        Route::delete('entries/{entry}', [EntryController::class, 'destroy'])->name('entries.destroy');
    });
});

require __DIR__.'/settings.php';
