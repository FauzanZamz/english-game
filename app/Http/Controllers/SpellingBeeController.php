<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Word, WordTheme, Game, Play, PlayEvent};
use App\Services\LexicoService;

class SpellingBeeController extends Controller
{
    public function index()
    {
        $themes = WordTheme::all();
        return view('spelling.index', compact('themes'));
    }

    public function newRound(Request $req, LexicoService $lex)
    {
        $req->validate(['theme'=>'required', 'level'=>'required|in:beginner,expert']);

        $theme = WordTheme::where('slug', $req->theme)->firstOrFail();
        $level = $req->level;

        $query = Word::where('theme_id', $theme->id);
        if ($level === 'beginner') {
            $query->whereBetween(DB::raw('LENGTH(text)'), [3,6]);
        } else {
            $query->where(DB::raw('LENGTH(text)'), '>', 6);
        }

        $candidates = $query->inRandomOrder()->limit(40)->pluck('text');

        foreach ($candidates as $w) {
            $data = $lex->get($w);
            if (!empty($data['defs']) && ($data['wiki']['extract'] ?? null)) {
                session([
                    'spelling_current' => $w,
                    'spelling_defs'    => $data['defs'],
                    'spelling_wiki'    => $data['wiki'],
                ]);

                $clues = array_map(function($d) use ($w){
                    return preg_replace("/\b" . preg_quote($w, '/') . "\b/i", '_____', $d);
                }, $data['defs']);

                return response()->json([
                    'wordAudio' => $w,
                    'clues'     => array_values($clues),
                    'wiki'      => $data['wiki'],
                ]);
            }
        }

        return response()->json(['error' => 'Tidak ditemukan kata valid, coba lagi'], 422);
    }

    public function answer(Request $req)
    {
        $req->validate(['answer'=>'nullable|string', 'giveup'=>'boolean']);
        $word = session('spelling_current');
        abort_unless($word, 400, 'Round belum dibuat');

        $correct = false;
        $scoreDelta = 0;
        $event = '';

        if ($req->boolean('giveup')) {
            $event = 'giveup';
        } else {
            $ans = strtolower(preg_replace('/\s+/', '', $req->input('answer','')));
            $correct = $ans === strtolower($word);
            $event = $correct ? 'correct' : 'wrong';
            $scoreDelta = $correct ? 10 : -2;
        }

        $events = session('spelling_events', []);
        $events[] = ['type'=>$event, 'word'=>$word, 'delta'=>$scoreDelta];
        session(['spelling_events'=>$events, 'spelling_current'=>null]);

        return response()->json([
            'correct'   => $correct,
            'expected'  => $word,
            'scoreDelta'=> $scoreDelta,
            'showWiki'  => session('spelling_wiki')
        ]);
    }

    public function finish(Request $req)
    {
        $req->validate(['duration_sec'=>'required|integer']);
        $events = session('spelling_events', []);

        $finalScore = 0;
        foreach ($events as $ev) $finalScore += $ev['delta'];

        $gameId = Game::where('slug','spelling-bee')->value('id');
        $play = Play::create([
            'user_id' => $req->user()->id,
            'game_id' => $gameId,
            'score'   => max(0, $finalScore),
            'duration_sec' => max(0, (int)$req->duration_sec),
        ]);

        foreach ($events as $ev) {
            PlayEvent::create([
                'play_id'=>$play->id,
                'type'=>$ev['type'],
                'payload'=>json_encode(['word'=>$ev['word'],'delta'=>$ev['delta']])
            ]);
        }

        // reset session
        session()->forget(['spelling_events','spelling_current','spelling_defs','spelling_wiki']);

        return response()->json(['ok'=>true, 'play_id'=>$play->id]);
    }
}
