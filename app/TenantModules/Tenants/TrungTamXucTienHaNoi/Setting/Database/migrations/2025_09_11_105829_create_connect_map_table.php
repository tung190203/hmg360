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
        Schema::create('connect_map', function (Blueprint $table) {
            $table->id();
            $table->integer('vrtour_id');
            $table->string('image');
            $table->string('image_en')->nullable();
            $table->longtext('content');
            $table->longtext('content_en')->nullable();
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connect_map');
    }
};
