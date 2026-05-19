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
        Schema::create('plan', function (Blueprint $table) {
            $table->id();
            $table->integer('vrtour_id');
            $table->integer('show')->default(0);
            $table->string('image1')->nullable();
            $table->string('image2')->nullable();
            $table->string('image3')->nullable();
            $table->string('image4')->nullable();
            $table->string('title1')->nullable();
            $table->string('title1_en')->nullable();
            $table->string('title2')->nullable();
            $table->string('title2_en')->nullable();
            $table->string('title3')->nullable();
            $table->string('title3_en')->nullable();
            $table->string('title4')->nullable();
            $table->string('title4_en')->nullable();
            $table->longtext('content1')->nullable();
            $table->longtext('content1_en')->nullable();
            $table->longtext('content2')->nullable();
            $table->longtext('content2_en')->nullable();
            $table->longtext('content3')->nullable();
            $table->longtext('content3_en')->nullable();
            $table->longtext('content4')->nullable();
            $table->longtext('content4_en')->nullable();
            $table->string('website')->nullable();
            $table->string('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan');
    }
};
