<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentProvider extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'owner_id',
        'display_name',
        'contact_email',
        'contact_phone',
        'description',
        'logo_url',
        'status',
        'revenue_share_percentage',
    ];

    protected $casts = [
        'revenue_share_percentage' => 'decimal:2',
    ];

    /**
     * Get the unique identifier column name.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the user that owns the provider.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the content items for the provider.
     */
    public function contentItems(): HasMany
    {
        return $this->hasMany(ContentItem::class, 'provider_id');
    }

    /**
     * Scope a query to only include approved providers.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Check if provider is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if provider is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
