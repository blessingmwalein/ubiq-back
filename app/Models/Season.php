<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'show_id',
        'season_number',
        'title',
        'description',
        'poster_url',
        'episode_count',
        'released_at',
    ];

    protected $casts = [
        'season_number' => 'integer',
        'episode_count' => 'integer',
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
     * Get the show that owns the season.
     */
    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class);
    }

    /**
     * Get the episodes for the season.
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class)->orderBy('episode_number');
    }

    /**
     * Update episode count.
     */
    public function updateEpisodeCount(): void
    {
        $this->update([
            'episode_count' => $this->episodes()->count(),
        ]);
    }
}
