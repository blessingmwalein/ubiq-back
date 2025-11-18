<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoAsset extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'episode_id',
        'content_item_id',
        'rendition_key',
        'hls_manifest_key',
        'hls_playlist_path',
        'bitrate',
        'resolution',
        'file_size_mb',
        'status',
    ];

    protected $casts = [
        'bitrate' => 'integer',
        'file_size_mb' => 'decimal:2',
    ];

    /**
     * Get the unique identifier column name.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the episode that owns the video asset.
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * Get the content item that owns the video asset.
     */
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }

    /**
     * Scope a query to only include ready assets.
     */
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    /**
     * Check if asset is ready.
     */
    public function isReady(): bool
    {
        return $this->status === 'ready';
    }
}
