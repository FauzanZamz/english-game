<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\CachedDef;

class LexicoService
{
    public function get(string $word): array
    {
        $word = strtolower($word);
        $cache = CachedDef::find($word);
        if ($cache && now()->diffInHours($cache->cached_at) < 24) {
            return [
                'defs' => json_decode($cache->definitions ?: '[]', true),
                'wiki' => ['extract'=>$cache->wiki_extract, 'image'=>$cache->wiki_image]
            ];
        }
        $defs = $this->fetchDefs($word);
        $wiki = $this->fetchWiki($word);
        CachedDef::updateOrCreate(['word'=>$word], [
            'definitions'=>json_encode($defs),
            'wiki_extract'=>$wiki['extract'] ?? null,
            'wiki_image'=>$wiki['image'] ?? null,
            'cached_at'=>now(),
        ]);
        return ['defs'=>$defs, 'wiki'=>$wiki];
    }

    protected function fetchDefs($word)
    {
        try {
            $res = Http::timeout(5)->get("https://api.dictionaryapi.dev/api/v2/entries/en/{$word}");
            if ($res->ok()){
                $arr = $res->json();
                if (is_array($arr) && count($arr)>0) {
                    $first = $arr[0]['meanings'] ?? [];
                    $m = collect($first)->flatMap(function($mm){
                        $pos = $mm['partOfSpeech'] ?? '';
                        return collect($mm['definitions'] ?? [])->map(function($d) use ($pos){
                            $def = is_array($d) ? ($d['definition'] ?? '') : (string)$d;
                            return "- (".$pos.") ".$def;
                        });
                    })->take(3)->values()->all();
                    return $m;
                }
            }
        } catch (\Throwable $e) {}
        return [];
    }

    protected function fetchWiki($word)
    {
        try {
            $res = Http::timeout(5)->withHeaders(['User-Agent'=>'EnglishEdu/1.0'])->get("https://en.wikipedia.org/api/rest_v1/page/summary/{$word}");
            if ($res->ok() && ($res['type'] ?? null) === 'standard'){
                $extract = $res['extract'] ?? '';
                $sent = preg_split('/(?<=[.!?]) +/', $extract);
                return [
                    'extract'=>implode(' ', array_slice($sent,0,2)),
                    'image'=>$res['originalimage']['source'] ?? null
                ];
            }
        } catch (\Throwable $e) {}
        return ['extract'=>null,'image'=>null];
    }
}
