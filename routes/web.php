<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SpellingBeeController;
use App\Http\Controllers\CrosswordController;
use App\Http\Controllers\LeaderboardController;

Route::redirect('/', '/dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Spelling Bee
    Route::get('/spelling', [SpellingBeeController::class,'index'])->name('spelling.index');
    Route::post('/spelling/new-round', [SpellingBeeController::class,'newRound'])->name('spelling.new');
    Route::post('/spelling/answer', [SpellingBeeController::class,'answer'])->name('spelling.answer');
    Route::post('/spelling/finish', [SpellingBeeController::class,'finish'])->name('spelling.finish');
    Route::post('/spelling/unlock', [SpellingBeeController::class,'unlockLevel'])->name('spelling.unlock');

    // Crossword
    Route::get('/crossword', [CrosswordController::class,'index'])->name('crossword.index');
    Route::post('/crossword/generate', [CrosswordController::class,'generate'])->name('crossword.generate');
    Route::post('/crossword/submit', [CrosswordController::class,'submit'])->name('crossword.submit');
    Route::post('/crossword/unlock', [CrosswordController::class,'unlockLevel'])->name('crossword.unlock');

    // Leaderboard
    Route::get('/leaderboard/spelling', [LeaderboardController::class,'spelling'])->name('leaderboard.spelling');
    Route::get('/leaderboard/crossword', [LeaderboardController::class,'crossword'])->name('leaderboard.crossword');
});

require __DIR__.'/auth.php';
