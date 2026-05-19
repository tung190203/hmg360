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
        Schema::create('investment_guides', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->integer('cat_id')->default(0);
            $table->integer('relic_id')->default(0);
            $table->string('image')->nullable();
            $table->integer('priority')->default(0);
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('source')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('is_hot')->default(0);
            $table->integer('view_num')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('language', 2)->default('vn');
            $table->text('files')->nullable();
            $table->string('short_file_descs')->nullable();
            $table->timestamps();
            $table->softDeletes(); // deleted_at

            $table->integer('project_type')->nullable();
            $table->integer('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_guides');
    }
};
