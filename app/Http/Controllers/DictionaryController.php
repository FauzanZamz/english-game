<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Word, WordTheme};
use App\Services\LexicoService;

class DictionaryController extends Controller
{
    /**
     * Daftar semua tema beserta kata-katanya.
     */
    public function index()
    {
        $themes = WordTheme::with(['words' => function ($q) {
            $q->orderBy('text');
        }])->orderBy('name')->get();

        return view('kamus.index', [
            'themes'     => $themes,
            'words'      => null,   // null = mode "browse by theme"
            'query'      => '',
            'activeSlug' => null,
        ]);
    }

    /**
     * Cari kata berdasarkan ?q= (LIKE) dengan paginate.
     * Bisa juga difilter per tema via ?theme=<slug>.
     */
    public function search(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $slug  = $request->query('theme');

        $themes = WordTheme::orderBy('name')->get();

        $words = Word::with('theme')
            ->when($query !== '', fn ($qb) => $qb->where('text', 'like', '%' . $query . '%'))
            ->when($slug, function ($qb) use ($slug) {
                $qb->whereHas('theme', fn ($t) => $t->where('slug', $slug));
            })
            ->orderBy('text')
            ->paginate(20)
            ->withQueryString();

        return view('kamus.index', [
            'themes'     => $themes,
            'words'      => $words,   // collection = mode "search results"
            'query'      => $query,
            'activeSlug' => $slug,
        ]);
    }

    /**
     * Detail satu kata: definisi + ringkasan Wikipedia via LexicoService.
     */
    public function show(string $word, LexicoService $lexico)
    {
        $data = $lexico->get($word);

        // Cari record Word untuk menampilkan badge tema (jika ada di DB).
        $record = Word::with('theme')
            ->whereRaw('LOWER(text) = ?', [strtolower($word)])
            ->first();

        return view('kamus.show', [
            'word'  => $word,
            'theme' => $record?->theme,
            'defs'  => $data['defs'] ?? [],
            'wiki'  => $data['wiki'] ?? ['extract' => null, 'image' => null],
        ]);
    }
}
