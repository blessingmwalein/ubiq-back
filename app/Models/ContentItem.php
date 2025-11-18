<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'provider_id',
        'category_id',
        'type',
        'title',
        'description',
        'duration_seconds',
        'poster_url',
        'backdrop_url',
        'thumbnail_url',
        'trailer_url',
        'genre',
        'tags',
        'metadata',
        'maturity_rating',
        'visibility',
        'views_count',
        'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
        'views_count' => 'integer',
        'duration_seconds' => 'integer',
        'published_at' => 'datetime',
    ];

    /**
     * Get the unique identifier column name.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the provider that owns the content.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(ContentProvider::class, 'provider_id');
    }

    /**
     * Get the category for the content.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the show details if this is a show.
     */
    public function show(): HasOne
    {
        return $this->hasOne(Show::class);
    }

    /**
     * Get the video assets for the content.
     */
    public function videoAssets(): HasMany
    {
        return $this->hasMany(VideoAsset::class);
    }

    /**
     * Get the episodes if this is a show.
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    /**
     * Get the transcoding jobs for the content.
     */
    public function transcodingJobs(): HasMany
    {
        return $this->hasMany(TranscodingJob::class);
    }

    /**
     * Get the watch history for the content.
     */
    public function watchHistory(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }

    /**
     * Get the favorites for the content.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Scope a query to only include published content.
     */
    public function scopePublished($query)
    {
        return $query->where('visibility', 'public')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include content by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeInCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Increment the views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Check if content is published.
     */
    public function isPublished(): bool
    {
        return $this->visibility === 'public'
            && $this->published_at
            && $this->published_at->isPast();
    }

    /**
     * Check if content is a movie.
     */
    public function isMovie(): bool
    {
        return $this->type === 'movie';
    }

    /**
     * Check if content is a show.
     */
    public function isShow(): bool
    {
        return $this->type === 'show';
    }
}
