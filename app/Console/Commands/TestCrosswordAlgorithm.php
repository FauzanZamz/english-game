<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Word;
use App\Models\WordTheme;
use App\Services\CrosswordBuilder;

class TestCrosswordAlgorithm extends Command
{
    protected $signature = 'crossword:test
        {--iterations=10 : Jumlah percobaan per kombinasi tema+level}
        {--theme=        : Filter tema tertentu berdasarkan slug (kosong = semua)}
        {--level=        : Filter level tertentu (kosong = semua)}
        {--export=       : Path file CSV untuk ekspor hasil (opsional)}';

    protected $description = 'Uji algoritma Greedy Maximum Intersection pada CrosswordBuilder untuk keperluan skripsi (Tabel 4.2)';

    protected array $levelConfig = [
        'beginner'     => ['count' => 5, 'minLen' => 4, 'maxLen' => 6,  'size' => 12],
        'intermediate' => ['count' => 6, 'minLen' => 5, 'maxLen' => 8,  'size' => 13],
        'expert'       => ['count' => 8, 'minLen' => 6, 'maxLen' => 10, 'size' => 15],
    ];

    public function handle(CrosswordBuilder $builder): int
    {
        $iterations  = max(1, (int) $this->option('iterations'));
        $themeFilter = $this->option('theme');
        $levelFilter = $this->option('level');
        $exportPath  = $this->option('export');

        // Validasi opsi --level
        $allLevels = array_keys($this->levelConfig);
        if ($levelFilter && !in_array($levelFilter, $allLevels)) {
            $this->error("Level tidak valid: '{$levelFilter}'. Pilih dari: " . implode(', ', $allLevels));
            return 1;
        }
        $levels = $levelFilter ? [$levelFilter] : $allLevels;

        // Ambil tema dari database
        $themesQuery = WordTheme::query();
        if ($themeFilter) {
            $themesQuery->where('slug', $themeFilter);
        }
        $themes = $themesQuery->orderBy('name')->get();

        if ($themes->isEmpty()) {
            $this->error('Tidak ada tema ditemukan' . ($themeFilter ? " dengan slug: '{$themeFilter}'" : '.'));
            return 1;
        }

        $this->info("Pengujian dimulai: {$themes->count()} tema × " . count($levels) . " level × {$iterations} iterasi");

        $summaryRows = [];
        $exportRows  = [];

        foreach ($themes as $theme) {
            foreach ($levels as $level) {
                $cfg    = $this->levelConfig[$level];
                $count  = $cfg['count'];
                $minLen = $cfg['minLen'];
                $maxLen = $cfg['maxLen'];
                $size   = $cfg['size'];

                $this->line('');
                $this->info("┌─ Tema: {$theme->name} | Level: " . strtoupper($level) . " | Grid: {$size}×{$size} | Target: {$count} kata");

                // Ambil kandidat kata dari tabel words
                $candidates = Word::where('theme_id', $theme->id)
                    ->whereBetween(DB::raw('LENGTH(text)'), [$minLen, $maxLen])
                    ->pluck('text')
                    ->map(fn ($w) => strtoupper(trim($w)))
                    ->unique()
                    ->values()
                    ->toArray();

                if (count($candidates) < 2) {
                    $this->warn("  [SKIP] Kata tidak cukup untuk tema '{$theme->name}' level '{$level}' "
                        . "(ditemukan: " . count($candidates) . ", minimum: 2). Lanjut ke kombinasi berikutnya.");
                    continue;
                }

                $bar = $this->output->createProgressBar($iterations);
                $bar->setFormat(" %current%/%max% [%bar%] %percent:3s%% – iterasi ke-%current%");
                $bar->start();

                $iterRows       = [];
                $totalPlaced    = 0;
                $totalIntersect = 0;
                $totalDensity   = 0;

                for ($iter = 1; $iter <= $iterations; $iter++) {
                    // Ambil subset kata sejumlah target dari pool kandidat
                    $pool = $candidates;
                    shuffle($pool);
                    $subset = array_slice($pool, 0, min($count, count($pool)));

                    // Random restart maksimal 10×; pilih hasil terbaik
                    $bestPlaced    = 0;
                    $bestIntersect = 0;
                    $bestOccupied  = 0;
                    $bestGrid      = null;
                    $bestPositions = [];

                    for ($restart = 0; $restart < 10; $restart++) {
                        $words = $subset;
                        shuffle($words);

                        try {
                            [$g, $pos] = $builder->build($words, $size);
                        } catch (\Throwable) {
                            continue;
                        }

                        $placed = count($pos);

                        // Hitung sel terisi di grid
                        $occupied = 0;
                        foreach ($g as $row) {
                            foreach ($row as $cell) {
                                if ($cell !== '') $occupied++;
                            }
                        }

                        // Total huruf semua kata terpasang
                        $totalLetters = 0;
                        foreach ($pos as $word => $info) {
                            $totalLetters += strlen($word);
                        }

                        // Intersection = total huruf - sel terisi
                        $intersect = $totalLetters - $occupied;

                        // Pilih terbaik: placed terbanyak, lalu intersect sebagai tiebreaker
                        if ($placed > $bestPlaced
                            || ($placed === $bestPlaced && $intersect > $bestIntersect)) {
                            $bestPlaced    = $placed;
                            $bestIntersect = $intersect;
                            $bestOccupied  = $occupied;
                            $bestGrid      = $g;
                            $bestPositions = $pos;
                        }
                    }

                    // Metrik iterasi ini
                    $density       = round(($bestOccupied / ($size * $size)) * 100, 2);
                    $placementRate = round(($bestPlaced / $count) * 100, 2);

                    $totalPlaced    += $bestPlaced;
                    $totalIntersect += $bestIntersect;
                    $totalDensity   += $density;

                    $iterRows[] = [
                        $iter,
                        $bestPlaced,
                        $bestIntersect,
                        $density . '%',
                        $placementRate . '%',
                    ];

                    $exportRows[] = [
                        'tema'           => $theme->name,
                        'level'          => $level,
                        'ukuran_grid'    => $size,
                        'target_kata'    => $count,
                        'iterasi'        => $iter,
                        'kata_terpasang' => $bestPlaced,
                        'interseksi'     => $bestIntersect,
                        'kerapatan_pct'  => $density,
                        'placement_rate' => $placementRate,
                    ];

                    $bar->advance();
                }

                $bar->finish();
                $this->line('');

                // Tabel detail per iterasi
                $this->table(
                    ['Iterasi', 'Kata Terpasang', 'Interseksi', 'Kerapatan Grid', 'Placement Rate'],
                    $iterRows
                );

                // Hitung rata-rata kombinasi ini
                $avgPlaced        = round($totalPlaced    / $iterations, 2);
                $avgIntersect     = round($totalIntersect / $iterations, 2);
                $avgDensity       = round($totalDensity   / $iterations, 2);
                $avgPlacementRate = round(($avgPlaced / $count) * 100, 2);

                $this->comment("  └ Rata-rata: Terpasang={$avgPlaced} | Interseksi={$avgIntersect} | Kerapatan={$avgDensity}% | Placement Rate={$avgPlacementRate}%");

                $summaryRows[] = [
                    $theme->name,
                    ucfirst($level),
                    "{$size}×{$size}",
                    $count,
                    $avgPlaced,
                    $avgPlacementRate . '%',
                    $avgIntersect,
                    $avgDensity . '%',
                ];
            }
        }

        // ── Tabel ringkasan akhir (Tabel 4.2 skripsi) ──
        $this->line('');
        $this->line('╔══════════════════════════════════════════════════════════════════╗');
        $this->info('  TABEL RINGKASAN PENGUJIAN ALGORITMA CROSSWORD (TABEL 4.2)');
        $this->line('╚══════════════════════════════════════════════════════════════════╝');

        if (!empty($summaryRows)) {
            $this->table(
                [
                    'Tema',
                    'Level',
                    'Ukuran Grid',
                    'Target Kata',
                    'Rata Terpasang',
                    'Placement Rate',
                    'Rata Interseksi',
                    'Rata Kerapatan',
                ],
                $summaryRows
            );
        } else {
            $this->warn('Tidak ada kombinasi yang berhasil diuji.');
        }

        // Ekspor CSV jika diminta
        if ($exportPath) {
            $this->exportCsv($exportPath, $exportRows);
        }

        $this->info('Pengujian selesai.');
        return 0;
    }

    private function exportCsv(string $path, array $rows): void
    {
        if (empty($rows)) {
            $this->warn('Tidak ada data untuk diekspor.');
            return;
        }

        $fp = fopen($path, 'w');
        if (!$fp) {
            $this->error("Gagal membuka file untuk ekspor: {$path}");
            return;
        }

        fputcsv($fp, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($fp, array_values($row));
        }
        fclose($fp);

        $this->info("Data per-iterasi berhasil diekspor ke: {$path}");
    }
}
