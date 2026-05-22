<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_events', function (Blueprint $table) {
            $table->string('event_id', 64)->primary();
            $table->string('event_type', 32)->nullable();
            $table->string('status', 32)->nullable();
            $table->string('mode', 32)->nullable();
            $table->integer('documents_uploaded')->nullable();
            $table->string('job_id', 64)->nullable();
            $table->string('doc_id', 64)->nullable();
            $table->string('source_filename', 500)->nullable();
            $table->integer('chunk_count')->nullable();
            $table->decimal('duration_s', 10, 2)->nullable();
            $table->integer('embedding_tokens')->nullable();
            $table->decimal('cost_usd_total', 12, 6)->default(0);
            $table->json('payload_json')->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamps();

            $table->index('received_at');
            $table->index('event_type');
            $table->index('job_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_events');
    }
};
