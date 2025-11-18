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
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('show_id')->constrained()->onDelete('cascade');
            $table->integer('season_number');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('poster_url')->nullable();
            $table->integer('episode_count')->default(0);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            
            $table->index('show_id');
            $table->unique(['show_id', 'season_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
