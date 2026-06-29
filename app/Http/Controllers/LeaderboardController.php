<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Game, Play};

class LeaderboardController extends Controller
{
    private const LEVELS = ['beginner', 'intermediate', 'expert'];
    private const LEVEL_LABELS = [
        'beginner'     => '🌱 Beginner',
        'intermediate' => '🌿 Intermediate',
        'expert'       => '🔥 Expert & Beyond',
    ];

    public function index(Request $request)
    {
        $gameSlug = $request->query('game', 'spelling-bee');
        $level    = $request->query('level', 'beginner');

        if (!in_array($gameSlug, ['spelling-bee', 'crossword'])) {
            $gameSlug = 'spelling-bee';
        }
        if (!in_array($level, self::LEVELS)) {
            $level = 'beginner';
        }

        $game = Game::where('slug', $gameSlug)->firstOrFail();

        $top = Play::with('user')
            ->where('game_id', $game->id)
            ->where('level', $level)
            ->orderByDesc('score')
            ->orderBy('duration_sec')
            ->limit(20)
            ->get();

        return view('leaderboard.index', [
            'game'        => $game,
            'gameSlug'    => $gameSlug,
            'level'       => $level,
            'top'         => $top,
            'levelLabels' => self::LEVEL_LABELS,
        ]);
    }

    /* Keep old named routes working (redirect ke unified) */
    public function spelling()
    {
        return redirect()->route('leaderboard.index', ['game' => 'spelling-bee']);
    }

    public function crossword()
    {
        return redirect()->route('leaderboard.index', ['game' => 'crossword']);
    }
}
