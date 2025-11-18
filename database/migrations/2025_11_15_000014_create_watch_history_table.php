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
        Schema::create('watch_history', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_item_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('episode_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('last_position_seconds')->default(0);
            $table->decimal('watched_percent', 5, 2)->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamp('last_watched_at');
            $table->timestamps();
            
            $table->index('profile_id');
            $table->index('content_item_id');
            $table->index('episode_id');
            $table->index(['profile_id', 'last_watched_at']);
            $table->unique(['profile_id', 'content_item_id', 'episode_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_history');
    }
};
