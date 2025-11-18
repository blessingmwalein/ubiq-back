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
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('provider_id')->constrained('content_providers')->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->enum('type', ['movie', 'show', 'skit', 'afrimation', 'real_estate'])->default('movie');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_seconds')->nullable(); // For movies, skits, etc.
            $table->text('poster_url')->nullable();
            $table->text('backdrop_url')->nullable();
            $table->text('trailer_url')->nullable();
            $table->json('tags')->nullable(); // ['action', 'comedy', 'thriller']
            $table->json('metadata')->nullable(); // {cast: [], director: "", year: 2025, etc.}
            $table->enum('maturity_rating', ['all', 'pg', 'pg13', 'r', 'adult'])->default('all');
            $table->enum('visibility', ['public', 'private', 'draft'])->default('draft');
            $table->integer('views_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('provider_id');
            $table->index('category_id');
            $table->index(['type', 'visibility']);
            $table->index('published_at');
            $table->index('views_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_items');
    }
};
