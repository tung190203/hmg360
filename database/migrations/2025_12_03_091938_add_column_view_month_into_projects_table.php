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
        Schema::table('projects', function(Blueprint $table) {
            $table->unsignedBigInteger('views_month')->default(0);
            $table->string('views_month_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function(Blueprint $table) {
            $table->dropColumn(['views_month', 'views_month_code']);
        });
    }
};
