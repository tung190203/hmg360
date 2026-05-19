<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hotspot', function (Blueprint $table) {
            $table->dropColumn('vrtour_lot_description');
            $table->longText('tooltip')->nullable()->change();
            $table->longText('tooltip_en')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('hotspot', function (Blueprint $table) {
            $table->longText('vrtour_lot_description')->nullable();
            $table->text('tooltip')->nullable()->change();
            $table->text('tooltip_en')->nullable()->change();
        });
    }
};
