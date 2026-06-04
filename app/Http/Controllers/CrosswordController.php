<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Word, WordTheme, Game, Play, PlayEvent, UserLevelUnlock};
use App\Services\{LexicoService, CrosswordBuilder, GbaService};

class CrosswordController extends Controller
{
    public function index()
    {
        $themes  = WordTheme::all();
        $unlocks = UserLevelUnlock::where('user_id', auth()->id())
            ->where('game', 'crossword')
            ->pluck('level');

        $unlockedLevels = [
            'intermediate' => $unlocks->contains('intermediate'),
            'expert'       => $unlocks->contains('expert'),
        ];

        return view('crossword.index', compact('themes', 'unlockedLevels'));
    }

    public function unlockLevel(Request $req)
    {
        $req->validate(['level' => 'required|in:intermediate,expert']);

        UserLevelUnlock::firstOrCreate([
            'user_id' => auth()->id(),
            'game'    => 'crossword',
            'level'   => $req->level,
        ], ['unlocked_at' => now()]);

        return response()->json(['ok' => true, 'level' => $req->level]);
    }

    /**
     * Kembalikan LD_target untuk sesi berikutnya (digunakan frontend saat generate).
     */
    public function nextLd(Request $req, GbaService $gba)
    {
        $ldNext = $gba->getNextLD($req->user()->id, 'crossword');
        return response()->json(['ld_next' => $ldNext]);
    }

    public function generate(Request $req, LexicoService $lex, CrosswordBuilder $builder, GbaService $gba)
    {
        $req->validate([
            'theme'     => 'required',
            'level'     => 'required|in:beginner,intermediate,expert',
            'ld_target' => 'nullable|numeric|min:0|max:1',
        ]);

        $ldTarget = $req->input('ld_target');

        if ($ldTarget !== null) {
            [$count, $minLen, $maxLen, $size] = $gba->ldToCrosswordParams((float) $ldTarget);
        } else {
            [$count, $minLen, $maxLen, $size] = match($req->level) {
                'beginner'     => [5, 4, 6, 12],
                'intermediate' => [6, 5, 8, 13],
                default        => [8, 6, 10, 15],
            };
        }

        session(['crossword_ld_target' => $ldTarget, 'crossword_level' => $req->level]);

        $theme = WordTheme::where('slug', $req->theme)->firstOrFail();

        $candidates = Word::where('theme_id', $theme->id)
            ->whereBetween(DB::raw('LENGTH(text)'), [$minLen, $maxLen])
            ->inRandomOrder()
            ->limit($count * 50)
            ->pluck('text')
            ->map(fn ($w) => strtolower(trim($w)))
            ->unique()
            ->values();

        if ($candidates->isEmpty()) {
            return response()->json(['error' => 'Tidak ada kata untuk tema/level ini.'], 422);
        }

        $picked = [];
        $defs   = [];
        foreach ($candidates as $w) {
            if (count($picked) >= $count) break;
            $data = $lex->get($w);
            $def  = $data['defs'][0] ?? null;
            if ($def) {
                $picked[]             = strtoupper($w);
                $defs[strtoupper($w)] = $def;
            }
        }

        if (count($picked) < max(3, $count - 2)) {
            return response()->json(['error' => 'Kata valid kurang. Coba ganti tema/level atau tambah data kata.'], 422);
        }

        $attempts = 10;
        $grid = $positions = null;
        while ($attempts-- > 0) {
            $words = $picked;
            shuffle($words);
            [$g, $pos] = $builder->build($words, $size);
            $placed = count($pos);
            if ($placed >= 3) { $grid = $g; $positions = $pos; break; }
        }

        if (!$grid) {
            return response()->json(['error' => 'Gagal membangun puzzle. Coba ulangi atau ganti tema/level.'], 422);
        }

        session(['crossword_solution' => $grid, 'crossword_positions' => $positions]);

        $defsFiltered = [];
        foreach ($positions as $word => $info) {
            if (isset($defs[$word])) $defsFiltered[$word] = $defs[$word];
        }

        return response()->json([
            'size'        => $size,
            'grid'        => $grid,
            'positions'   => $positions,
            'definitions' => $defsFiltered,
        ]);
    }

    public function submit(Request $req, GbaService $gba)
    {
        $req->validate([
            'grid'            => 'required|array',
            'duration_sec'    => 'nullable|integer',
            'hints_used'      => 'nullable|integer',
            'hints_available' => 'nullable|integer',
        ]);

        $solution  = session('crossword_solution');
        $positions = session('crossword_positions', []);
        abort_if(!$solution, 400, 'Belum generate puzzle');

        // ── Per-word scoring ──
        $wordsCorrect = 0;
        $totalWords   = count($positions);
        $wordResults  = [];

        foreach ($positions as $word => $pos) {
            $allRight = true;
            for ($i = 0; $i < $pos['length']; $i++) {
                if ($pos['direction'] === 'across') {
                    $r = $pos['row'];
                    $c = $pos['col'] + $i;
                } else {
                    $r = $pos['row'] + $pos['length'] - 1 - $i;
                    $c = $pos['col'];
                }
                $entered  = strtoupper($req->grid[$r][$c] ?? '');
                $expected = $solution[$r][$c] ?? '';
                if ($entered !== $expected) { $allRight = false; break; }
            }
            $wordResults[$word] = $allRight;
            if ($allRight) $wordsCorrect++;
        }

        $score = $totalWords > 0
            ? ($wordsCorrect === $totalWords ? 100 : (int) floor($wordsCorrect / $totalWords * 100))
            : 0;

        $isExpert    = session('crossword_level') === 'expert';
        $durationSec = (int) $req->input('duration_sec', 0);
        $ldTarget    = null;
        $theta       = null;
        $ldNext      = null;

        // GBA/DDA hanya aktif untuk difficulty Expert & Beyond
        if ($isExpert) {
            $ldTarget   = (float) (session('crossword_ld_target') ?? GbaService::LD_INITIAL);
            $hintsUsed  = (int) $req->input('hints_used', 0);
            $hintsAvail = (int) $req->input('hints_available', 3);
            $success    = $totalWords > 0 && ($wordsCorrect / $totalWords) >= 0.6;
            $levelNum   = Play::where('user_id', $req->user()->id)
                ->whereHas('game', fn ($q) => $q->where('slug', 'crossword'))
                ->count() + 1;

            [$theta, $ldNext] = $gba->calculateTheta(
                currentLD:      $ldTarget,
                success:        $success,
                hintsUsed:      $hintsUsed,
                hintsAvail:     $hintsAvail,
                isInitialLevel: $levelNum <= 2
            );

            $gba->saveLog(
                $req->user()->id, 'crossword', $levelNum,
                $theta, $ldTarget, $ldNext, $success, $durationSec
            );
        }

        $gameId = Game::where('slug', 'crossword')->value('id');
        $play   = Play::create([
            'user_id'      => $req->user()->id,
            'game_id'      => $gameId,
            'score'        => $score,
            'duration_sec' => $durationSec,
            'ld_target'    => $ldTarget,
            'theta_result' => $theta,
        ]);

        PlayEvent::create([
            'play_id' => $play->id,
            'type'    => 'finish',
            'payload' => json_encode([
                'wordsCorrect' => $wordsCorrect,
                'totalWords'   => $totalWords,
                'wordResults'  => $wordResults,
            ]),
        ]);

        session()->forget(['crossword_solution', 'crossword_positions', 'crossword_ld_target', 'crossword_level']);

        $response = [
            'wordsCorrect' => $wordsCorrect,
            'totalWords'   => $totalWords,
            'score'        => $score,
            'play_id'      => $play->id,
        ];
        if ($isExpert && $theta !== null) {
            $response['theta']   = round($theta, 3);
            $response['ld_next'] = round($ldNext, 3);
        }
        return response()->json($response);
    }
}
