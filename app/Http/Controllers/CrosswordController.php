<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Word, WordTheme, Game, Play, PlayEvent};
use App\Services\{LexicoService, CrosswordBuilder};

class CrosswordController extends Controller
{
    public function index()
    {
        $themes = WordTheme::all();
        return view('crossword.index', compact('themes'));
    }

    /**
     * Generate puzzle seperti ttsv2.py:
     * - pilih kata by tema & level (panjang tertentu)
     * - validasi definisi (dictionaryapi.dev)
     * - build grid dengan interseksi (CrosswordBuilder)
     * - kembalikan grid + definitions + positions
     */
    public function generate(Request $req, LexicoService $lex, CrosswordBuilder $builder)
    {
        $req->validate(['theme' => 'required', 'level' => 'required|in:beginner,expert']);

        // Parameter per level (meniru ttsv2)
        [$count, $minLen, $maxLen, $size] =
            $req->level === 'beginner' ? [5, 4, 6, 12] : [8, 6, 10, 15];

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

        return response()->json([
            'size'        => $size,
            'grid'        => $grid,        // matriks huruf ('' untuk blok)
            'positions'   => $positions,   // koordinat & arah tiap kata
            'definitions' => $defsFiltered // clue
        ]);
    }

    /**
     * Pengecekan jawaban, mirip ttsv2: benar per huruf + bonus full
     */
    public function submit(Request $req)
    {
        $req->validate(['grid' => 'required|array', 'duration_sec' => 'nullable|integer']);
        $solution = session('crossword_solution');
        abort_if(!$solution, 400, 'Belum generate puzzle');

        $correct = 0; $total = 0;
        foreach ($solution as $r => $row) {
            foreach ($row as $c => $cell) {
                if ($cell) { // hanya sel berhuruf yang dihitung
                    $total++;
                    $inp = strtoupper($req->grid[$r][$c] ?? '');
                    if ($inp === $cell) $correct++;
                }
            }
        }

        $score = $correct + (($correct === $total) ? 10 : 0);

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
            'payload' => json_encode(['correct' => $correct, 'total' => $total])
        ]);

        // hapus kunci supaya satu puzzle satu submit
        session()->forget('crossword_solution');

        return response()->json(['correct' => $correct, 'total' => $total, 'score' => $score, 'play_id' => $play->id]);
    }
}
