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
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedTinyInteger('approval_level')->default(0); // 0 = chưa duyệt, 1 = cấp 1 duyệt, 2 = hoàn tất
            $table->unsignedTinyInteger('max_approval')->default(2);   // tổng số cấp cần duyệt
            $table->boolean('is_draft')->default(false);
            $table->foreignId('parent_id')->nullable();
            $table->string('status')->default('pending'); // pending | approved | rejected
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['approval_level', 'max_approval', 'is_draft', 'parent_id', 'status']);
        });
    }
};
