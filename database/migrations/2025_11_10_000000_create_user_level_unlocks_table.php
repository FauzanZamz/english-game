<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_level_unlocks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('game');
            $t->string('level');
            $t->timestamp('unlocked_at')->useCurrent();
            $t->timestamps();
            $t->unique(['user_id', 'game', 'level']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_level_unlocks');
    }
};
