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
        Schema::table('users', function (Blueprint $table) {
            $table->string('social_provider')->nullable()->after('password');
            $table->string('social_provider_id')->nullable()->after('social_provider');
            $table->text('avatar_url')->nullable()->after('social_provider_id');
            $table->timestamp('trial_ends_at')->nullable()->after('avatar_url');
            $table->enum('role', ['customer', 'provider', 'admin'])->default('customer')->after('trial_ends_at');
            
            $table->index(['social_provider', 'social_provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['social_provider', 'social_provider_id']);
            $table->dropColumn(['social_provider', 'social_provider_id', 'avatar_url', 'trial_ends_at', 'role']);
        });
    }
};
