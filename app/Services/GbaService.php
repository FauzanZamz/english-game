<?php

namespace App\Services;

use App\Models\PlayerGbaLog;

class GbaService
{
    const K           = 0.10;
    const B_INITIAL   = 0.15;
    const LD_INITIAL  = 0.30;
    const LD_MIN      = 0.05;
    const LD_MAX      = 0.95;

    /**
     * Hitung θ (player performance) dan LD_next berdasarkan hasil sesi.
     * Formula dari jurnal: Husniah et al. (2025)
     *
     * a = K × (RL / RT)
     * SUCCESS: θ = LD + a
     * FAIL:    θ = LD − a  (atau −b untuk level awal)
     *
     * @return array [θ, LD_next]
     */
    public function calculateTheta(
        float $currentLD,
        bool  $success,
        int   $hintsUsed    = 0,
        int   $hintsAvail   = 3,
        bool  $isInitialLevel = false
    ): array {
        $rl = max(0, $hintsAvail - $hintsUsed);
        $rt = max(1, $hintsAvail);
        $a  = self::K * ($rl / $rt);

        if ($success) {
            $theta = $currentLD + $a;
        } elseif ($isInitialLevel) {
            $theta = $currentLD - self::B_INITIAL;
        } else {
            $theta = $currentLD - $a;
        }

        $theta  = max(self::LD_MIN, min(self::LD_MAX, $theta));
        $ldNext = $theta;

        return [$theta, $ldNext];
    }

    /**
     * Hitung Difficulty Level (LD) berdasarkan kriteria.
     * LD = (Σ wi × criteria_i) / Σwi
     */
    public function calculateLD(string $game, array $criteria): float
    {
        $weights = $game === 'spelling'
            ? ['word_length_norm' => 0.40, 'word_count_norm' => 0.30, 'hint_norm' => 0.20, 'time_norm' => 0.10]
            : ['word_length_norm' => 0.35, 'word_count_norm' => 0.30, 'grid_norm' => 0.20, 'hint_norm' => 0.15];

        $numerator = $denominator = 0;
        foreach ($weights as $key => $w) {
            $numerator   += $w * ($criteria[$key] ?? 0);
            $denominator += $w;
        }

        return $denominator > 0
            ? max(self::LD_MIN, min(self::LD_MAX, $numerator / $denominator))
            : self::LD_INITIAL;
    }

    /**
     * Mapping LD → [min_len, max_len] panjang kata (Spelling Bee).
     * LD rendah = kata pendek (mudah), LD tinggi = kata panjang (sulit).
     */
    public function ldToWordLength(float $ld): array
    {
        return match(true) {
            $ld < 0.25 => [3, 4],
            $ld < 0.40 => [4, 5],
            $ld < 0.55 => [5, 7],
            $ld < 0.70 => [6, 9],
            default    => [8, 15],
        };
    }

    /**
     * Mapping LD → [word_count, min_len, max_len, grid_size] (Crossword).
     */
    public function ldToCrosswordParams(float $ld): array
    {
        return match(true) {
            $ld < 0.25 => [4, 3, 5, 10],
            $ld < 0.40 => [5, 4, 6, 11],
            $ld < 0.55 => [6, 5, 8, 12],
            $ld < 0.70 => [7, 6, 9, 13],
            default    => [8, 7, 12, 15],
        };
    }

    /**
     * Dapatkan LD_target untuk sesi berikutnya dari GBA log.
     * Jika belum ada history, kembalikan LD_INITIAL.
     */
    public function getNextLD(int $userId, string $game): float
    {
        $lastLog = PlayerGbaLog::where('user_id', $userId)
            ->where('game', $game)
            ->orderByDesc('level_num')
            ->first();

        return $lastLog ? (float) $lastLog->ld_next : self::LD_INITIAL;
    }

    /**
     * Simpan GBA log ke database.
     */
    public function saveLog(
        int    $userId,
        string $game,
        int    $levelNum,
        float  $theta,
        float  $ld,
        float  $ldNext,
        bool   $success,
        int    $durationSec,
        array  $criteriaSnapshot = []
    ): void {
        PlayerGbaLog::create([
            'user_id'           => $userId,
            'game'              => $game,
            'level_num'         => $levelNum,
            'theta'             => round($theta, 4),
            'ld'                => round($ld, 4),
            'ld_next'           => round($ldNext, 4),
            'success'           => $success,
            'duration_sec'      => $durationSec,
            'criteria_snapshot' => $criteriaSnapshot,
        ]);
    }
}
