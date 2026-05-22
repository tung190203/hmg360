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
        Schema::create('hotspot', function (Blueprint $table) {
            $table->id();
            $table->integer('vrtour_id');
            $table->string('potision');
            $table->string('url');
            $table->string('url_en')->nullable();
            $table->float('opacity', 2);
            $table->string('tooltip')->nullable();
            $table->string('tooltip_en')->nullable();
            $table->integer('type');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotspot');
    }
};
