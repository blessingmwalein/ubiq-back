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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('key')->unique(); // e.g., 'free_trial', 'basic', 'premium', 'family'
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('max_profiles')->default(1);
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->json('features')->nullable(); // ['feature1', 'feature2']
            $table->boolean('is_active')->default(true);
            $table->integer('trial_days')->default(0);
            $table->timestamps();
            
            $table->index('key');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
