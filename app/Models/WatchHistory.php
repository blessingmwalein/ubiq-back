<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchHistory extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'watch_history';

    protected $fillable = [
        'profile_id',
        'content_item_id',
        'episode_id',
        'last_position_seconds',
        'watched_percent',
        'completed',
        'last_watched_at',
    ];

    protected $casts = [
        'last_position_seconds' => 'integer',
        'watched_percent' => 'decimal:2',
        'completed' => 'boolean',
        'last_watched_at' => 'datetime',
    ];

    /**
     * Get the unique identifier column name.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the profile that owns the watch history.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the content item for the watch history.
     */
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }

    /**
     * Get the episode for the watch history.
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * Update progress.
     */
    public function updateProgress(int $positionSeconds, int $durationSeconds): void
    {
        $percent = $durationSeconds > 0 ? ($positionSeconds / $durationSeconds) * 100 : 0;
        
        $this->update([
            'last_position_seconds' => $positionSeconds,
            'watched_percent' => round($percent, 2),
            'completed' => $percent >= 90,
            'last_watched_at' => now(),
        ]);
    }
}
