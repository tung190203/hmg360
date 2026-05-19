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

        Schema::table('hotspot', function (Blueprint $table) {
            $table->tinyInteger('unit')->after('acreage')->default(0)->comment('0: ha, 1: km');
        });

        Schema::table('industrial_projects', function (Blueprint $table) {
            $table->tinyInteger('unit')->after('acreage')->default(0)->comment('0: ha, 1: km');
        });
    }

    public function down(): void
    {
        Schema::table('hotspot', function ($table) {
            $table->dropColumn('unit');
        });
        Schema::table('industrial_projects', function ($table) {
            $table->dropColumn('unit');
        });
    }
};
