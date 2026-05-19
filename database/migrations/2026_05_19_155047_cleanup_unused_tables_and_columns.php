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
        Schema::table('investment_guides', function (Blueprint $table) {
            $table->dropColumn('relic_id');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('relic_id');
        });
        Schema::dropIfExists('destinations');
        Schema::dropIfExists('artifacts');
        Schema::dropIfExists('relics');
        Schema::dropIfExists('feedbacks');
        Schema::dropIfExists('slugs');
        Schema::dropIfExists('space_360');
        Schema::dropIfExists('tourist_routes');
        Schema::dropIfExists('tours');
        Schema::dropIfExists('widgets');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('relics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('parent_id')->default(0);
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->integer('priority')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->string('language', 2)->default('vn');
            $table->timestamps();
        });

        Schema::create('destinations', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug');

            $table->integer('cat_id')->default(0);
            $table->integer('relic_id')->nullable()->default(0);

            $table->string('image')->nullable();
            $table->text('list_image')->nullable();

            $table->string('address')->nullable();
            $table->string('url_vrtour')->nullable();

            $table->integer('priority')->default(0);

            $table->text('info')->nullable();
            $table->longText('content')->nullable();

            $table->string('source')->nullable();

            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('is_hot')->default(0);

            $table->integer('view_num')->default(0);

            $table->string('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();

            $table->string('language', 2)->default('vn');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('artifacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');

            $table->integer('cat_id')->default(0);
            $table->integer('relic_id')->nullable()->default(0);

            $table->string('image')->nullable();
            $table->string('file_3d')->nullable();
            $table->string('address')->nullable();

            $table->integer('priority')->default(0);

            $table->string('size')->nullable();

            $table->text('list_image')->nullable();
            $table->text('vrtour_iframe')->nullable();

            $table->text('description')->nullable();
            $table->text('content')->nullable();

            $table->tinyInteger('status')->default(0);
            $table->integer('view_num')->default(0);

            $table->string('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();

            $table->string('language', 2)->default('vn');

            $table->timestamps();
        });

        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email');

            $table->string('phone')->nullable();

            $table->integer('country_id')->nullable()->default(0);

            $table->string('address')->nullable();

            $table->text('content')->nullable();

            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('type')->default(0);

            $table->string('language', 2)->default('vn');

            $table->timestamp('booking_at')->nullable();

            $table->timestamps();
        });

        Schema::create('slugs', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->nullable()->index();
            $table->string('module');
            $table->integer('module_id')->default(0);
            $table->timestamps();
        });

        Schema::create('space_360', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug');

            $table->integer('cat_id')->default(0);
            $table->integer('relic_id')->nullable()->default(0);

            $table->string('image')->nullable();
            $table->text('list_image')->nullable();

            $table->integer('priority')->nullable()->default(0);

            $table->text('description')->nullable();
            $table->text('content')->nullable();

            $table->string('url_vrtour')->nullable();
            $table->string('address')->nullable();

            $table->tinyInteger('status')->nullable()->default(0);
            $table->integer('view_num')->nullable()->default(0);

            $table->string('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();

            $table->string('language', 2)->nullable()->default('vn');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tourist_routes', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug');

            $table->string('place_name');
            $table->string('address');

            $table->string('url_vrtour');

            $table->integer('cat_id')->default(0);

            $table->string('destination_ids', 1000)
                ->nullable()
                ->default(0);

            $table->string('image')->nullable();

            $table->integer('priority')
                ->nullable()
                ->default(0);

            $table->text('description')->nullable();
            $table->text('content')->nullable();

            $table->tinyInteger('status')
                ->nullable()
                ->default(0);

            $table->string('language', 2)
                ->nullable()
                ->default('vn');

            $table->timestamps();
        });

         Schema::create('tours', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->timestamps();
        });

        Schema::create('widgets', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('image')->nullable();
            $table->string('link')->nullable();

            $table->string('position');

            $table->integer('priority')
                ->nullable()
                ->default(0);

            $table->text('description')->nullable();

            $table->tinyInteger('status')
                ->nullable()
                ->default(0);

            $table->string('language', 2)
                ->nullable()
                ->default('vn');

            $table->timestamps();
        });

        Schema::table('investment_guides', function (Blueprint $table) {
            $table->integer('relic_id')->default(0)->after('cat_id');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->integer('relic_id')->default(0)->after('cat_id');
        });
    }
};