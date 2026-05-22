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
            $table->text('name')->comment('Project Name')->change();
            $table->text('slug')->comment('Project Slug')->change();
            $table->text('link')->nullable()->comment('Link to Project')->change();
            $table->text('link_vrtour')->nullable()->comment('Virtual Tour Link')->change();
            $table->text('link_sand_table')->nullable()->comment('Link to Sand Table')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('name')->comment('Project Name')->change();
            $table->string('slug')->comment('Project Slug')->change();
            $table->string('link')->nullable()->comment('Link to Project')->change();
            $table->string('link_vrtour')->nullable()->comment('Virtual Tour Link')->change();
            $table->string('link_sand_table')->nullable()->comment('Link to Sand Table')->change();
        });
    }
};
