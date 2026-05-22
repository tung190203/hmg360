<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hotspot') && ! Schema::hasColumn('hotspot', 'unit')) {
            Schema::table('hotspot', function (Blueprint $table) {
                $table->tinyInteger('unit')->after('acreage')->default(0)->comment('0: ha, 1: km');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hotspot') && Schema::hasColumn('hotspot', 'unit')) {
            Schema::table('hotspot', function (Blueprint $table) {
                $table->dropColumn('unit');
            });
        }
    }
};
