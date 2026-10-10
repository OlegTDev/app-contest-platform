<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Contest management (public pages for authenticated users)
    Route::get('/', [ContestController::class, 'publicIndex'])->name('contests.public');
    Route::get('contests/{contest}', [ContestController::class, 'publicShow'])->name('contests.public.show');

    // Admin user management
    Route::prefix('admin/users')->middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [Admin\UserController::class, 'index'])->name('admin.users.index');
        Route::patch('/{user}/role', [Admin\UserController::class, 'updateRole'])->name('admin.users.update-role');
    });

    // Admin contest management (moderators only)
    Route::prefix('admin/contests')->middleware(['moderator'])->group(function () {
        Route::get('/', [ContestAdminController::class, 'index'])->name('admin.contests.index');
        Route::get('/create', [ContestAdminController::class, 'create'])->name('admin.contests.create');
        Route::post('/', [ContestAdminController::class, 'store'])->name('admin.contests.store');
        Route::get('/{contest}', [ContestAdminController::class, 'show'])->name('admin.contests.show');
        Route::get('/{contest}/edit', [ContestAdminController::class, 'edit'])->name('admin.contests.edit');
        Route::patch('/{contest}', [ContestAdminController::class, 'update'])->name('admin.contests.update');
        Route::delete('/{contest}', [ContestAdminController::class, 'destroy'])->name('admin.contests.destroy');
        Route::patch('/{contest}/status', [ContestAdminController::class, 'updateStatus'])->name('admin.contests.status');
    });

    // Contest activity management and participation (kept under the legacy
    // "admin/contests" prefix: owners manage entries/media/quizzes here, while
    // participants use it for voting and quiz runs; public duplicates live
    // under the "contest/{contest}" prefix).
    Route::prefix('admin/contests')->group(function () {
        // Entry management (general routes first, then parameterized)
        Route::get('/{contest}/entries', [EntryController::class, 'index'])->name('admin.entries.index');
        Route::get('/{contest}/entries/create', [EntryController::class, 'create'])->name('admin.entries.create');
        Route::post('/{contest}/entries', [EntryController::class, 'store'])->name('admin.entries.store');
        Route::get('/{contest}/entries/leaderboard', [VoteController::class, 'leaderboard'])->name('admin.entries.leaderboard');
        Route::get('/{contest}/entries/{entry}/edit', [EntryController::class, 'edit'])->name('admin.entries.edit');
        Route::patch('/{contest}/entries/{entry}', [EntryController::class, 'update'])->name('admin.entries.update');
        Route::delete('/{contest}/entries/{entry}', [EntryController::class, 'destroy'])->name('admin.entries.destroy');

        // Media management (general routes first, then parameterized)
        Route::get('/{contest}/media', [MediaController::class, 'index'])->name('admin.media.index');
        Route::post('/{contest}/media', [MediaController::class, 'store'])->name('admin.media.store');
        Route::delete('/{contest}/media/{media}', [MediaController::class, 'destroy'])->name('admin.media.destroy');
        Route::get('/{contest}/entries/{entry}/media', [MediaController::class, 'entryIndex'])->name('admin.entry.media.index');
        Route::post('/{contest}/entries/{entry}/media', [MediaController::class, 'entryStore'])->name('admin.entry.media.store');
        Route::delete('/{contest}/entries/{entry}/media/{media}', [MediaController::class, 'entryDestroy'])->name('admin.entry.media.destroy');
        Route::post('/{contest}/entries/{entry}/media/{media}/main', [MediaController::class, 'toggleMain'])->name('admin.entry.media.toggleMain');

        // Voting
        Route::post('/{contest}/entries/{entry}/vote', [VoteController::class, 'store'])->name('admin.entries.vote');
        Route::delete('/{contest}/entries/{entry}/vote', [VoteController::class, 'destroy'])->name('admin.entries.vote.destroy');

        // Quiz management (general routes first, then parameterized)
        Route::get('/{contest}/quizzes', [QuizController::class, 'index'])->name('admin.quizzes.index');
        Route::get('/{contest}/quizzes/create', [QuizController::class, 'create'])->name('admin.quizzes.create');
        Route::post('/{contest}/quizzes', [QuizController::class, 'store'])->name('admin.quizzes.store');
        Route::get('/{contest}/quizzes/{quiz}/take', [QuizController::class, 'take'])->name('admin.quizzes.take');
        Route::get('/{contest}/quizzes/{quiz}/result', [QuizController::class, 'result'])->name('admin.quizzes.result');
        Route::get('/{contest}/quizzes/{quiz}/leaderboard', [QuizController::class, 'leaderboard'])->name('admin.quizzes.leaderboard');
        Route::post('/{contest}/quizzes/{quiz}/submit', [QuizController::class, 'submit'])->name('admin.quizzes.submit');
        Route::get('/{contest}/quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('admin.quizzes.edit');
        Route::patch('/{contest}/quizzes/{quiz}', [QuizController::class, 'update'])->name('admin.quizzes.update');
        Route::delete('/{contest}/quizzes/{quiz}', [QuizController::class, 'destroy'])->name('admin.quizzes.destroy');
    });

    // Contest detail (public pages for authenticated users)
    Route::prefix('contest/{contest}')->group(function () {
        // Public voting
        Route::post('entries/{entry}/vote', [VoteController::class, 'store'])->name('entries.vote');
        Route::delete('entries/{entry}/vote', [VoteController::class, 'destroy'])->name('entries.vote.destroy');
        Route::get('entries/leaderboard', [VoteController::class, 'leaderboard'])->name('entries.leaderboard');

        // Quiz participation
        Route::get('quizzes/{quiz}/take', [QuizController::class, 'take'])->name('quizzes.take');
        Route::post('quizzes/{quiz}/submit', [QuizController::class, 'submit'])->name('quizzes.submit');
        Route::get('quizzes/{quiz}/result', [QuizController::class, 'result'])->name('quizzes.result');
        Route::get('quizzes/{quiz}/leaderboard', [QuizController::class, 'leaderboard'])->name('quizzes.leaderboard');
    });
});

require __DIR__.'/settings.php';
