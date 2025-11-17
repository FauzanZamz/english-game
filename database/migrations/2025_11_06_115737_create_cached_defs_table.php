<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cached_defs', function (Blueprint $t) {
            $t->string('word')->primary();
            $t->json('definitions')->nullable();
            $t->text('wiki_extract')->nullable();
            $t->string('wiki_image')->nullable();
            $t->timestamp('cached_at')->nullable();
        });
    }
    public function down(): void {
        Schema::dropIfExists('cached_defs');
    }
};
