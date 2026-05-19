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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Project Name');
            $table->string('slug')->unique()->comment('Project Slug');
            $table->text('short_desc')->nullable()->comment('Short Description of Project');
            $table->text('description')->nullable()->comment('Project Description');
            $table->double('lat')->nullable()->comment('Latitude');
            $table->double('lng')->nullable()->comment('Longitude');
            $table->double('area')->nullable()->comment('Area in square meters');
            $table->unsignedBigInteger('type_number')->nullable()->comment('Type of Project');
            $table->unsignedBigInteger('industry_number')->nullable()->comment('Industry of Project');
            $table->bigInteger('price')->nullable()->comment('Price in VND');
            $table->string('link')->nullable()->comment('Link to Project');
            $table->text('banner_image')->nullable()->comment('Image URL');
            $table->text('location_image')->nullable()->comment('Location Image URL');
            $table->text('advantage_images')->nullable()->comment('Advantage Images');
            $table->text('advantage_titles')->nullable()->comment('Advantage Texts');
            $table->text('advantage_descriptions')->nullable()->comment('Advantage Descriptions');
            $table->string('link_vrtour')->nullable()->comment('Virtual Tour Link');
            $table->string('link_sand_table')->nullable()->comment('Link to Sand Table');
            $table->text('design_short_desc')->nullable()->comment('Design Short Description');
            $table->text('design_images')->nullable()->comment('Design Images');
            $table->text('design_description')->nullable()->comment('Design Description');
            $table->text('legal_short_desc')->nullable()->comment('Legal Short Description');
            $table->text('legal_description')->nullable()->comment('Legal Description');
            $table->unsignedBigInteger('layout_id')->nullable()->comment('Layout ID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
