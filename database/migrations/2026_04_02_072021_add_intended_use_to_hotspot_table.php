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
            $table->text('intended_use')->nullable()->comment("Mục đích sử dụng")->after('acreage');
            $table->longText('vrtour_lot_description')->nullable()->comment("Mô tả thông tin lô đất bên vrtour")->after('intended_use');
        });
    }

    public function down(): void
    {
        Schema::table('hotspot', function (Blueprint $table) {
            $table->dropColumn('intended_use');
            $table->dropColumn('vrtour_lot_description');
        });
    }
};
