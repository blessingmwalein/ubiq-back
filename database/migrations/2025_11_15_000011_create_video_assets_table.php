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
        Schema::create('video_assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('episode_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('content_item_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('rendition_key'); // e.g., '1080p', '720p', '480p', '360p'
            $table->text('hls_manifest_key'); // S3 key for master.m3u8
            $table->text('hls_playlist_path')->nullable(); // S3 path to playlist files
            $table->integer('bitrate')->nullable(); // in kbps
            $table->string('resolution')->nullable(); // e.g., '1920x1080'
            $table->decimal('file_size_mb', 10, 2)->nullable();
            $table->enum('status', ['processing', 'ready', 'failed'])->default('processing');
            $table->timestamps();
            
            $table->index('episode_id');
            $table->index('content_item_id');
            $table->index(['status', 'rendition_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_assets');
    }
};
