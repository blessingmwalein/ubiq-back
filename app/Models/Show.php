<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Show extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'content_item_id',
        'total_seasons',
        'total_episodes',
        'status',
    ];

    protected $casts = [
        'total_seasons' => 'integer',
        'total_episodes' => 'integer',
    ];

    /**
     * Get the unique identifier column name.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the content item for the show.
     */
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }

    /**
     * Get the seasons for the show.
     */
    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class)->orderBy('season_number');
    }

    /**
     * Get all episodes through seasons.
     */
    public function episodes(): HasManyThrough
    {
        return $this->hasManyThrough(Episode::class, Season::class);
    }

    /**
     * Update totals.
     */
    public function updateTotals(): void
    {
        $this->update([
            'total_seasons' => $this->seasons()->count(),
            'total_episodes' => $this->episodes()->count(),
        ]);
    }
}
