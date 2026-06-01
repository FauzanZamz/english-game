<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Word, WordTheme, Game, Play, PlayEvent, UserLevelUnlock};
use App\Services\{LexicoService, CrosswordBuilder};

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

    public function generate(Request $req, LexicoService $lex, CrosswordBuilder $builder)
    {
        $req->validate(['theme' => 'required', 'level' => 'required|in:beginner,intermediate,expert']);

        // Parameter per level
        [$count, $minLen, $maxLen, $size] = match($req->level) {
            'beginner'     => [5, 4, 6, 12],
            'intermediate' => [6, 5, 8, 13],
            default        => [8, 6, 10, 15],  // expert
        };

        // Ambil tema
        $theme = WordTheme::where('slug', $req->theme)->firstOrFail();

        // Kandidat kata (dari DB) sesuai panjang
        $candidates = Word::where('theme_id', $theme->id)
            ->whereBetween(DB::raw('LENGTH(text)'), [$minLen, $maxLen])
            ->inRandomOrder()
            ->limit($count * 50) // banyak supaya chance berhasil tinggi
            ->pluck('text')
            ->map(fn ($w) => strtolower(trim($w)))
            ->unique()
            ->values();

        if ($candidates->isEmpty()) {
            return response()->json(['error' => 'Tidak ada kata untuk tema/level ini.'], 422);
        }

        // Pilih kata yang punya definisi (seperti get_valid_words di ttsv2)
        $picked = [];
        $defs   = []; // word => definition (string pendek)
        foreach ($candidates as $w) {
            if (count($picked) >= $count) break;
            $data = $lex->get($w);
            $def  = $data['defs'][0] ?? null;
            if ($def) {
                $picked[] = strtoupper($w);
                $defs[strtoupper($w)] = $def;
            }
        }

        if (count($picked) < max(3, $count - 2)) {
            return response()->json(['error' => 'Kata valid kurang. Coba ganti tema/level atau tambah data kata.'], 422);
        }

        // Coba bangun grid beberapa kali agar stabil
        $attempts = 10;
        $grid = $positions = null;
        while ($attempts-- > 0) {
            // acak urutan kata agar penempatan bervariasi
            $words = $picked;
            shuffle($words);
            [$g, $pos] = $builder->build($words, $size);
            // Kriteria sederhana: minimal 3 kata terpasang di grid
            $placed = 0;
            foreach ($pos as $info) { $placed++; }
            if ($placed >= 3) { $grid = $g; $positions = $pos; break; }
        }

        if (!$grid) {
            return response()->json(['error' => 'Gagal membangun puzzle. Coba ulangi atau ganti tema/level.'], 422);
        }

        // Simpan solusi di session untuk pengecekan saat submit
        session(['crossword_solution' => $grid]);

        // Kirim definisi hanya untuk kata yang benar-benar ada di posisi
        $defsFiltered = [];
        foreach ($positions as $word => $info) {
            if (isset($defs[$word])) $defsFiltered[$word] = $defs[$word];
        }

        // Simpan positions di session untuk per-word scoring saat submit
        session(['crossword_positions' => $positions]);

        return response()->json([
            'size'        => $size,
            'grid'        => $grid,        // matriks huruf ('' untuk blok)
            'positions'   => $positions,   // koordinat & arah tiap kata
            'definitions' => $defsFiltered // clue
        ]);
    }

    
    public function submit(Request $req)
    {
        $req->validate(['grid' => 'required|array', 'duration_sec' => 'nullable|integer']);
        $solution  = session('crossword_solution');
        $positions = session('crossword_positions', []);
        abort_if(!$solution, 400, 'Belum generate puzzle');

        // ── Per-word scoring (max 100 poin) ──
        // Setiap kata dinilai benar/salah secara keseluruhan.
        // Skor = (kata_benar / total_kata) × 100, dibulatkan ke bawah.
        // Jika semua kata benar → tepat 100.
        //
        // Koordinat setelah array_reverse di CrosswordBuilder:
        //   ACROSS : row = pos.row,  col = pos.col .. pos.col + length - 1
        //   DOWN   : huruf pertama di baris pos.row + length - 1 (atas visual),
        //            turun ke pos.row (bawah visual) → r = pos.row+length-1-i
        $wordsCorrect = 0;
        $totalWords   = count($positions);
        $wordResults  = [];   // word → bool (untuk PlayEvent)

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

        // Simpan play
        $gameId = Game::where('slug', 'crossword')->value('id');
        $play = Play::create([
            'user_id'      => $req->user()->id,
            'game_id'      => $gameId,
            'score'        => $score,
            'duration_sec' => (int) $req->input('duration_sec', 0),
        ]);
        PlayEvent::create([
            'play_id' => $play->id,
            'type'    => 'finish',
            'payload' => json_encode([
                'wordsCorrect' => $wordsCorrect,
                'totalWords'   => $totalWords,
                'wordResults'  => $wordResults,
            ])
        ]);

        // hapus kunci supaya satu puzzle satu submit
        session()->forget(['crossword_solution', 'crossword_positions']);

        return response()->json([
            'wordsCorrect' => $wordsCorrect,
            'totalWords'   => $totalWords,
            'score'        => $score,
            'play_id'      => $play->id,
        ]);
    }
}
