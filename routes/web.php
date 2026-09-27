<?php

use App\Http\Controllers\ContestController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('contest', ContestController::class);
});

require __DIR__.'/settings.php';
