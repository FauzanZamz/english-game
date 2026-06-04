<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('player_gba_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('game');               // 'spelling' atau 'crossword'
            $t->unsignedInteger('level_num'); // urutan sesi (1, 2, 3, …)
            $t->decimal('theta', 5, 4);       // θ estimasi kemampuan [0–1]
            $t->decimal('ld', 5, 4);          // Difficulty Level sesi ini [0–1]
            $t->decimal('ld_next', 5, 4);     // LD untuk sesi berikutnya [0–1]
            $t->boolean('success');
            $t->unsignedInteger('duration_sec')->default(0);
            $t->json('criteria_snapshot')->nullable();
            $t->timestamps();
        });

        Schema::table('plays', function (Blueprint $t) {
            $t->decimal('ld_target', 5, 4)->nullable()->after('score');
            $t->decimal('theta_result', 5, 4)->nullable()->after('ld_target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_gba_logs');
        Schema::table('plays', function (Blueprint $t) {
            $t->dropColumn(['ld_target', 'theta_result']);
        });
    }
};
