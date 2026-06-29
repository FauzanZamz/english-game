<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SpellingBeeController;
use App\Http\Controllers\CrosswordController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\DictionaryController;

Route::redirect('/', '/dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Spelling Bee
    Route::get('/spelling', [SpellingBeeController::class, 'index'])->name('spelling.index');
    Route::get('/spelling/next-ld', [SpellingBeeController::class, 'nextLd'])->name('spelling.next-ld');
    Route::post('/spelling/new-round', [SpellingBeeController::class, 'newRound'])->name('spelling.new');
    Route::post('/spelling/answer', [SpellingBeeController::class, 'answer'])->name('spelling.answer');
    Route::post('/spelling/finish', [SpellingBeeController::class, 'finish'])->name('spelling.finish');
    Route::post('/spelling/unlock', [SpellingBeeController::class, 'unlockLevel'])->name('spelling.unlock');

    // Crossword
    Route::get('/crossword', [CrosswordController::class, 'index'])->name('crossword.index');
    Route::get('/crossword/next-ld', [CrosswordController::class, 'nextLd'])->name('crossword.next-ld');
    Route::post('/crossword/generate', [CrosswordController::class, 'generate'])->name('crossword.generate');
    Route::post('/crossword/submit', [CrosswordController::class, 'submit'])->name('crossword.submit');
    Route::post('/crossword/unlock', [CrosswordController::class, 'unlockLevel'])->name('crossword.unlock');

    // Kamus (Dictionary) — search HARUS sebelum {word} agar tidak tertangkap wildcard
    Route::get('/kamus', [DictionaryController::class, 'index'])->name('kamus.index');
    Route::get('/kamus/search', [DictionaryController::class, 'search'])->name('kamus.search');
    Route::get('/kamus/{word}', [DictionaryController::class, 'show'])->name('kamus.show');

    // Leaderboard (unified)
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
    // Legacy redirects — agar link lama di Spelling Bee tidak 404
    Route::get('/leaderboard/spelling', [LeaderboardController::class, 'spelling'])->name('leaderboard.spelling');
    Route::get('/leaderboard/crossword', [LeaderboardController::class, 'crossword'])->name('leaderboard.crossword');

    // Assessment Dashboard (GBA)
    Route::get('/assessment/dashboard', [AssessmentController::class, 'index'])->name('assessment.dashboard');
});

require __DIR__.'/auth.php';
