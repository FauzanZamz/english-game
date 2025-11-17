<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('words', function (Blueprint $t) {
            $t->id();
            $t->foreignId('theme_id')->constrained('word_themes')->cascadeOnDelete();
            $t->string('text');
            $t->index(['theme_id','text']);
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('words');
    }
};
