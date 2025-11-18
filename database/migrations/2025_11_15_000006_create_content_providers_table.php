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
        Schema::create('content_providers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('display_name');
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->text('description')->nullable();
            $table->text('logo_url')->nullable();
            $table->enum('status', ['pending', 'approved', 'suspended', 'rejected'])->default('pending');
            $table->decimal('revenue_share_percentage', 5, 2)->default(70.00); // Provider gets 70%
            $table->timestamps();
            
            $table->index('owner_id');
            $table->index('status');
            $table->index('contact_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_providers');
    }
};
