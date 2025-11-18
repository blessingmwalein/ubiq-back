<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaybackToken extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'profile_id',
        'content_item_id',
        'episode_id',
        'token',
        'device_id',
        'client_ip',
        'user_agent',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Get the unique identifier column name.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the profile that owns the token.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the content item for the token.
     */
    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }

    /**
     * Get the episode for the token.
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * Scope a query to only include valid tokens.
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now())
            ->whereNull('used_at');
    }

    /**
     * Check if token is valid.
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture() && !$this->used_at;
    }

    /**
     * Mark token as used.
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }
}
