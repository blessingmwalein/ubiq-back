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
        Schema::table('content_items', function (Blueprint $table) {
            // Drop the foreign key first
            $table->dropForeign(['category_id']);
            
            // Modify column to be nullable
            $table->foreignId('category_id')->nullable()->change()->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreignId('category_id')->nullable(false)->change()->constrained()->onDelete('restrict');
        });
    }
};
