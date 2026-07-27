<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cached_defs', function (Blueprint $table) {
            $table->text('definitions_id')->nullable()->after('definitions');
            $table->text('wiki_extract_id')->nullable()->after('wiki_extract');
        });
    }

    public function down(): void
    {
        Schema::table('cached_defs', function (Blueprint $table) {
            $table->dropColumn(['definitions_id', 'wiki_extract_id']);
        });
    }
};
