<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_events', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_events', 'documents_failed')) {
                $table->integer('documents_failed')->nullable()->after('documents_uploaded');
            }
            if (!Schema::hasColumn('ai_events', 'new_slot')) {
                $table->string('new_slot', 8)->nullable()->after('documents_failed');
            }
        });

        Schema::table('ai_events', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('ai_events', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('ai_events', function (Blueprint $table) {
            if (Schema::hasColumn('ai_events', 'new_slot')) {
                $table->dropColumn('new_slot');
            }
            if (Schema::hasColumn('ai_events', 'documents_failed')) {
                $table->dropColumn('documents_failed');
            }
        });
    }
};
