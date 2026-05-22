<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->json('name')->nullable();
            $table->json('slug')->nullable();
            $table->json('description')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->json('meta_description')->nullable();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->unsignedTinyInteger('type')->default(1);
            $table->unsignedInteger('priority')->default(0);
            $table->string('image')->nullable();
            $table->boolean('at_home')->default(false);
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
