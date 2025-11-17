<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Game, Play};

class LeaderboardController extends Controller
{
    public function spelling()
    {
        $game = Game::where('slug','spelling-bee')->firstOrFail();
        $top = Play::with('user')->where('game_id',$game->id)
            ->orderByDesc('score')->orderBy('duration_sec')->limit(20)->get();
        return view('leaderboard.index', compact('top','game'));
    }

    public function crossword()
    {
        $game = Game::where('slug','crossword')->firstOrFail();
        $top = Play::with('user')->where('game_id',$game->id)
            ->orderByDesc('score')->orderBy('duration_sec')->limit(20)->get();
        return view('leaderboard.index', compact('top','game'));
    }
}
