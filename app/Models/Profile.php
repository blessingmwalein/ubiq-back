<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'account_id',
        'name',
        'avatar_url',
        'maturity_rating',
        'is_primary',
        'is_kids',
        'pin_enabled',
        'pin',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_kids' => 'boolean',
        'pin_enabled' => 'boolean',
    ];

    protected $hidden = [
        'pin',
    ];

    /**
     * Get the unique identifier column name.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the account that owns the profile.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the watch history for the profile.
     */
    public function watchHistory(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }

    /**
     * Get the favorites for the profile.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the favorite content items.
     */
    public function favoriteContent(): BelongsToMany
    {
        return $this->belongsToMany(ContentItem::class, 'favorites')
            ->withTimestamps();
    }

    /**
     * Get the interests for the profile.
     */
    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Interest::class, 'profile_interest')
            ->withTimestamps();
    }

    /**
     * Get the playback tokens for the profile.
     */
    public function playbackTokens(): HasMany
    {
        return $this->hasMany(PlaybackToken::class);
    }

    /**
     * Check if profile can view content based on maturity rating.
     */
    public function canView(string $contentRating): bool
    {
        $ratings = ['all', 'pg', 'pg13', 'r', 'adult'];
        $profileLevel = array_search($this->maturity_rating, $ratings);
        $contentLevel = array_search($contentRating, $ratings);

        return $profileLevel !== false && $contentLevel !== false && $profileLevel >= $contentLevel;
    }

    /**
     * Verify PIN for profile access.
     */
    public function verifyPin(?string $pin): bool
    {
        // If PIN is not enabled, allow access
        if (!$this->pin_enabled) {
            return true;
        }

        // If PIN is required but not provided, deny access
        if (empty($pin)) {
            return false;
        }

        // Verify PIN matches
        return hash_equals($this->pin, hash('sha256', $pin));
    }

    /**
     * Set PIN for profile.
     */
    public function setPin(?string $pin): void
    {
        if (empty($pin)) {
            $this->pin_enabled = false;
            $this->pin = null;
        } else {
            $this->pin_enabled = true;
            $this->pin = hash('sha256', $pin);
        }
    }

    /**
     * Get continue watching content.
     */
    public function continueWatching()
    {
        return $this->watchHistory()
            ->where('completed', false)
            ->where('watched_percent', '>', 5)
            ->orderBy('last_watched_at', 'desc')
            ->limit(10);
    }
}
