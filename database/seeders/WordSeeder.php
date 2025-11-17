<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{WordTheme, Word, Game};

class WordSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            'animals' => 'Animals',
            'fruits-vegetables' => 'Fruits & Vegetables',
            'jobs' => 'Jobs',
            'music-instruments' => 'Music Instruments',
        ];
        foreach ($themes as $slug=>$name) {
            $theme = WordTheme::firstOrCreate(['slug'=>$slug], ['name'=>$name]);
            $file = storage_path('app/words/'.str_replace('-', '_', $slug).'.json');
            if (file_exists($file)) {
                $list = json_decode(file_get_contents($file), true) ?: [];
                foreach ($list as $w) {
                    Word::firstOrCreate(['theme_id'=>$theme->id, 'text'=>strtolower($w)]);
                }
            }
        }
        Game::firstOrCreate(['slug'=>'spelling-bee'], ['name'=>'Spelling Bee']);
        Game::firstOrCreate(['slug'=>'crossword'], ['name'=>'Crossword']);
    }
}
