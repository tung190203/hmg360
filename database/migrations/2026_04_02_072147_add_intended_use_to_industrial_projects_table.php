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
            $table->text('intended_use')->nullable()->comment("Mục đích sử dụng")->after('acreage');
        });
    }

    public function down(): void
    {
        Schema::table('industrial_projects', function (Blueprint $table) {
            $table->dropColumn('intended_use');
        });
    }
};
