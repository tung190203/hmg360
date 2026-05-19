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
        Schema::table('industrial_projects', function (Blueprint $table) {
            $table->longText('link')
                ->nullable()
                ->comment('Liên kết dự án')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('industrial_projects', function (Blueprint $table) {
            $table->string('link')
                ->nullable()
                ->comment('Liên kết dự án')
                ->change();
        });
    }
};
