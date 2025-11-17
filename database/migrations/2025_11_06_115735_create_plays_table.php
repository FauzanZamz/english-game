<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('plays', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $t->unsignedInteger('score')->default(0);
            $t->unsignedInteger('duration_sec')->default(0);
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('plays');
    }
};
