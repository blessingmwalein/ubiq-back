<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Episode extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'season_id',
        'content_item_id',
        'episode_number',
        'title',
        'description',
        'duration_seconds',
        'thumbnail_url',
        'video_master_key',
        'views_count',
        'released_at',
    ];

    protected $casts = [
        'episode_number' => 'integer',
        'duration_seconds' => 'integer',
        'views_count' => 'integer',
        'released_at' => 'datetime',
    ];

    /**
     * Get the unique identifier column name.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the season that owns the episode.
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Get the content item for the episode.
     */
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }

    /**
     * Get the video assets for the episode.
     */
    public function videoAssets(): HasMany
    {
        return $this->hasMany(VideoAsset::class);
    }

    /**
     * Get the transcoding jobs for the episode.
     */
    public function transcodingJobs(): HasMany
    {
        return $this->hasMany(TranscodingJob::class);
    }

    /**
     * Get the watch history for the episode.
     */
    public function watchHistory(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }

    /**
     * Get the playback tokens for the episode.
     */
    public function playbackTokens(): HasMany
    {
        return $this->hasMany(PlaybackToken::class);
    }

    /**
     * Increment the views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
