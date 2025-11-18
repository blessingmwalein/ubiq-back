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
        Schema::create('transcoding_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('content_item_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('episode_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('source_key'); // S3 key of master video
            $table->text('output_path')->nullable(); // S3 path for transcoded outputs
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued');
            $table->integer('progress_percentage')->default(0);
            $table->json('result')->nullable(); // {renditions: [], errors: [], processing_time: 0}
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('content_item_id');
            $table->index('episode_id');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcoding_jobs');
    }
};
