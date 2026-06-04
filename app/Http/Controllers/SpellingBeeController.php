<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Word, WordTheme, Game, Play, PlayEvent, UserLevelUnlock};
use App\Services\{LexicoService, GbaService};

class SpellingBeeController extends Controller
{
    public function index()
    {
        $themes  = WordTheme::all();
        $unlocks = UserLevelUnlock::where('user_id', auth()->id())
            ->where('game', 'spelling')
            ->pluck('level');

        $unlockedLevels = [
            'intermediate' => $unlocks->contains('intermediate'),
            'expert'       => $unlocks->contains('expert'),
        ];

        return view('spelling.index', compact('themes', 'unlockedLevels'));
    }

    public function unlockLevel(Request $req)
    {
        $req->validate(['level' => 'required|in:intermediate,expert']);

        UserLevelUnlock::firstOrCreate([
            'user_id' => auth()->id(),
            'game'    => 'spelling',
            'level'   => $req->level,
        ], ['unlocked_at' => now()]);

        return response()->json(['ok' => true, 'level' => $req->level]);
    }

    /**
     * Kembalikan LD_target untuk sesi berikutnya (digunakan frontend saat start).
     */
    public function nextLd(Request $req, GbaService $gba)
    {
        $ldNext = $gba->getNextLD($req->user()->id, 'spelling');
        return response()->json(['ld_next' => $ldNext]);
    }

    public function newRound(Request $req, LexicoService $lex, GbaService $gba)
    {
        $req->validate([
            'theme'     => 'required',
            'level'     => 'required|in:beginner,intermediate,expert',
            'ld_target' => 'nullable|numeric|min:0|max:1',
        ]);

        $theme    = WordTheme::where('slug', $req->theme)->firstOrFail();
        $level    = $req->level;
        $ldTarget = $req->input('ld_target');

        $query = Word::where('theme_id', $theme->id);

        if ($ldTarget !== null) {
            [$minLen, $maxLen] = $gba->ldToWordLength((float) $ldTarget);
            $query->whereBetween(DB::raw('LENGTH(text)'), [$minLen, $maxLen]);
        } else {
            match($level) {
                'beginner'     => $query->whereBetween(DB::raw('LENGTH(text)'), [3, 5]),
                'intermediate' => $query->whereBetween(DB::raw('LENGTH(text)'), [5, 7]),
                default        => $query->where(DB::raw('LENGTH(text)'), '>', 7),
            };
        }

        $candidates = $query->inRandomOrder()->limit(40)->pluck('text');

        foreach ($candidates as $w) {
            $data = $lex->get($w);
            if (!empty($data['defs']) && ($data['wiki']['extract'] ?? null)) {
                session([
                    'spelling_current'   => $w,
                    'spelling_defs'      => $data['defs'],
                    'spelling_wiki'      => $data['wiki'],
                    'spelling_ld_target' => $ldTarget,
                    'spelling_level'     => $req->level,
                ]);

                $clues = array_map(function ($d) use ($w) {
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
        $req->validate(['answer' => 'nullable|string', 'giveup' => 'boolean']);
        $word = session('spelling_current');
        abort_unless($word, 400, 'Round belum dibuat');

        $correct    = false;
        $scoreDelta = 0;
        $event      = '';

        if ($req->boolean('giveup')) {
            $event = 'giveup';
        } else {
            $ans     = strtolower(preg_replace('/\s+/', '', $req->input('answer', '')));
            $correct = $ans === strtolower($word);
            $event   = $correct ? 'correct' : 'wrong';
            $scoreDelta = $correct ? 10 : -2;
        }

        $events   = session('spelling_events', []);
        $events[] = ['type' => $event, 'word' => $word, 'delta' => $scoreDelta];
        session(['spelling_events' => $events, 'spelling_current' => null]);

        return response()->json([
            'correct'    => $correct,
            'expected'   => $word,
            'scoreDelta' => $scoreDelta,
            'showWiki'   => session('spelling_wiki'),
        ]);
    }

    public function finish(Request $req, GbaService $gba)
    {
        $req->validate([
            'duration_sec'    => 'required|integer',
            'hints_used'      => 'nullable|integer',
            'hints_available' => 'nullable|integer',
        ]);

        $events = session('spelling_events', []);

        $finalScore   = 0;
        $correctCount = 0;
        $totalCount   = count($events);
        foreach ($events as $ev) {
            $finalScore += $ev['delta'];
            if ($ev['type'] === 'correct') $correctCount++;
        }

        $isExpert    = session('spelling_level') === 'expert';
        $durationSec = (int) $req->duration_sec;
        $ldTarget    = null;
        $theta       = null;
        $ldNext      = null;

        // GBA/DDA hanya aktif untuk difficulty Expert & Beyond
        if ($isExpert) {
            $ldTarget   = (float) (session('spelling_ld_target') ?? GbaService::LD_INITIAL);
            $hintsUsed  = (int) $req->input('hints_used', 0);
            $hintsAvail = (int) $req->input('hints_available', 3);
            $success    = $totalCount > 0 && ($correctCount / $totalCount) >= 0.6;
            $levelNum   = Play::where('user_id', $req->user()->id)
                ->whereHas('game', fn ($q) => $q->where('slug', 'spelling-bee'))
                ->count() + 1;

            [$theta, $ldNext] = $gba->calculateTheta(
                currentLD:      $ldTarget,
                success:        $success,
                hintsUsed:      $hintsUsed,
                hintsAvail:     $hintsAvail,
                isInitialLevel: $levelNum <= 2
            );

            $gba->saveLog(
                $req->user()->id, 'spelling', $levelNum,
                $theta, $ldTarget, $ldNext, $success, $durationSec
            );
        }

        $gameId = Game::where('slug', 'spelling-bee')->value('id');
        $play   = Play::create([
            'user_id'      => $req->user()->id,
            'game_id'      => $gameId,
            'score'        => max(0, $finalScore),
            'duration_sec' => max(0, $durationSec),
            'ld_target'    => $ldTarget,
            'theta_result' => $theta,
        ]);

        foreach ($events as $ev) {
            PlayEvent::create([
                'play_id' => $play->id,
                'type'    => $ev['type'],
                'payload' => json_encode(['word' => $ev['word'], 'delta' => $ev['delta']]),
            ]);
        }

        session()->forget(['spelling_events', 'spelling_current', 'spelling_defs', 'spelling_wiki', 'spelling_ld_target', 'spelling_level']);

        $response = ['ok' => true, 'play_id' => $play->id];
        if ($isExpert && $theta !== null) {
            $response['theta']   = round($theta, 3);
            $response['ld_next'] = round($ldNext, 3);
            $response['success'] = $success;
        }
        return response()->json($response);
    }
}
