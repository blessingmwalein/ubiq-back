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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_item_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index('profile_id');
            $table->index('content_item_id');
            $table->unique(['profile_id', 'content_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
