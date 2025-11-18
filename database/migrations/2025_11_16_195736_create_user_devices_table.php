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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_id')->unique(); // Unique device identifier from client
            $table->string('device_name'); // e.g., "iPhone 14 Pro", "MacBook Pro"
            $table->enum('device_type', ['mobile', 'tablet', 'desktop', 'tv', 'other'])->default('other');
            $table->string('os_name')->nullable(); // iOS, Android, Windows, macOS, etc.
            $table->string('os_version')->nullable();
            $table->string('app_version')->nullable();
            $table->string('browser_name')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('location')->nullable(); // City, Country
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('device_id');
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
