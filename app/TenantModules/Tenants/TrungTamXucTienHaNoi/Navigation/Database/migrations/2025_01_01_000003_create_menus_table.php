<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->json('name')->nullable();
            $table->json('slug')->nullable();
            $table->json('custom_link')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('page_id')->nullable();
            $table->foreignId('cat_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->unsignedInteger('priority')->default(0);
            $table->unsignedTinyInteger('status')->default(1);
            $table->string('type')->default('main');
            $table->boolean('is_mega')->default(false);
            $table->string('language', 10)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
