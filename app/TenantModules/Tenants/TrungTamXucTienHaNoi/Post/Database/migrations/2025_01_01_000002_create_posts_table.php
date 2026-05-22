<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->json('name')->nullable();
            $table->json('slug')->nullable();
            $table->foreignId('cat_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->unsignedBigInteger('relic_id')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->json('description')->nullable();
            $table->json('content')->nullable();
            $table->string('source')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->boolean('is_hot')->default(false);
            $table->unsignedBigInteger('view_num')->default(0);
            $table->json('meta_title')->nullable();
            $table->json('meta_keywords')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('language', 10)->nullable();
            $table->string('project_type')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
