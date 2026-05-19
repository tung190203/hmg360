<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('investor', function (Blueprint $table) {
            $table->string('image')->nullable()->change();
            $table->string('website')->nullable()->change();
            $table->string('sologan')->nullable()->change();
        });
        DB::statement('ALTER TABLE investor MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        Schema::table('investor', function (Blueprint $table) {
            $table->string('image')->nullable(false)->change();
            $table->string('website')->nullable(false)->change();
            $table->string('sologan')->nullable(false)->change();
        });
    }
};
