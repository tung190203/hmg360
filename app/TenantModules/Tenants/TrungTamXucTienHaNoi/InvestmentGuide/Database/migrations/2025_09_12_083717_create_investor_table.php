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
        Schema::create('investor', function (Blueprint $table) {
            $table->id();
            $table->integer('vrtour_id');
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('image');
            $table->longtext('content1')->nullable();
            $table->longtext('content1_en')->nullable();
            $table->longtext('content2')->nullable();
            $table->longtext('content2_en')->nullable();
            $table->longtext('content3')->nullable();
            $table->longtext('content3_en')->nullable();
            $table->string('website');
            $table->string('sologan');
            $table->string('sologan_en')->nullable();
            $table->integer('status');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor');
    }
};
