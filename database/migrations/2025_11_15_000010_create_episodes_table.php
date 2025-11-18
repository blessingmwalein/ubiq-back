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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_item_id')->constrained()->onDelete('cascade');
            $table->integer('episode_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_seconds');
            $table->text('thumbnail_url')->nullable();
            $table->string('video_master_key')->nullable(); // S3 key for master video
            $table->integer('views_count')->default(0);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            
            $table->index('season_id');
            $table->index('content_item_id');
            $table->unique(['season_id', 'episode_number']);
            $table->index('views_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
