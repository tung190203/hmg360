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
        Schema::create('industrial_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->comment('Thuộc ID dự án');
            $table->string('name')->comment('Tên dự án');
            $table->string('code')->unique()->comment('Mã dự án');
            $table->string('link')->nullable()->comment('Liên kết dự án');
            $table->decimal('acreage', 10, 2)->nullable()->comment('Diện tích (hectare)');
            $table->text('description')->nullable()->comment('Mô tả dự án');
            $table->unsignedBigInteger('product_type')->nullable()->comment('Loại hình sản phẩm');
            $table->bigInteger('price')->nullable()->comment('Price in VND');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('industrial_projects');
    }
};
