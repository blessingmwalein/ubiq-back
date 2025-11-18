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
        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('pin_enabled')->default(false)->after('is_primary');
            $table->string('pin')->nullable()->after('pin_enabled');
            $table->boolean('is_kids')->default(false)->after('maturity_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['pin_enabled', 'pin', 'is_kids']);
        });
    }
};
