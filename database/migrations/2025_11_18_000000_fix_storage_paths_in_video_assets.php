<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix video asset paths - remove 'public/' prefix
        DB::table('video_assets')
            ->where('hls_manifest_key', 'LIKE', 'public/%')
            ->update([
                'hls_manifest_key' => DB::raw("SUBSTRING(hls_manifest_key, 8)"), // Remove first 7 chars ('public/')
                'hls_playlist_path' => DB::raw("SUBSTRING(hls_playlist_path, 8)"),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add 'public/' prefix back
        DB::table('video_assets')
            ->whereNotNull('hls_manifest_key')
            ->whereRaw("hls_manifest_key NOT LIKE 'public/%'")
            ->update([
                'hls_manifest_key' => DB::raw("CONCAT('public/', hls_manifest_key)"),
                'hls_playlist_path' => DB::raw("CONCAT('public/', hls_playlist_path)"),
            ]);
    }
};
