<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'account_id',
        'package_id',
        'provider',
        'provider_subscription_id',
        'status',
        'billing_cycle',
        'is_trial',
        'started_at',
        'current_period_start',
        'current_period_end',
        'expires_at',
        'cancelled_at',
    ];

    protected $casts = [
        'is_trial' => 'boolean',
        'started_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the unique identifier column name.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the account that owns the subscription.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the package for the subscription.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the payments for the subscription.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Check if subscription is on trial.
     */
    public function isOnTrial(): bool
    {
        return $this->is_trial && $this->isActive();
    }

    /**
     * Check if subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->cancelled_at !== null;
    }

    /**
     * Cancel subscription.
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Renew subscription.
     */
    public function renew(\DateTime $periodStart, \DateTime $periodEnd): void
    {
        $this->update([
            'status' => 'active',
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
            'expires_at' => $periodEnd,
        ]);
    }
}
