<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('panorama', function (Blueprint $table) {
            if (!Schema::hasColumn('panorama', 'label_audio')) {
                $table->string('label_audio')->nullable()->after('vrtour_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('panorama', function (Blueprint $table) {
            if (Schema::hasColumn('panorama', 'label_audio')) {
                $table->dropColumn('label_audio');
            }
        });
    }
};
