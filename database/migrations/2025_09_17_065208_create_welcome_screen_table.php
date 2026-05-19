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
        Schema::create('welcome_screen', function (Blueprint $table) {
            $table->id();
            $table->integer('vrtour_id');
            $table->string('title');
            $table->longtext('description');
            $table->integer('show_investor')->default(0);
            $table->string('investor_img')->nullable();
            $table->string('investor_desc1')->nullable();
            $table->string('investor_desc2')->nullable();
            $table->string('investor_desc3')->nullable();
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welcome_screen');
    }
};
