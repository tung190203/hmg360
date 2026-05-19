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
        Schema::create('panorama', function (Blueprint $table) {
            $table->id();
            $table->integer('vrtour_id');
            $table->string('ids');
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->longtext('content')->nullable();
            $table->longtext('content_en')->nullable();
            $table->string('audio')->nullable();
            $table->string('audio_en')->nullable();
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panorama');
    }
};
