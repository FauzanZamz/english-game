<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('play_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('play_id')->constrained('plays')->cascadeOnDelete();
            $t->string('type');
            $t->json('payload')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('play_events');
    }
};
