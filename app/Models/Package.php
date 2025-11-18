<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'key',
        'title',
        'description',
        'max_profiles',
        'price_monthly',
        'price_yearly',
        'features',
        'is_active',
        'trial_days',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'trial_days' => 'integer',
    ];

    /**
     * Get the unique identifier column name.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the accounts using this package.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get the subscriptions for this package.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Check if package is free.
     */
    public function isFree(): bool
    {
        return $this->price_monthly == 0 && $this->price_yearly == 0;
    }

    /**
     * Get yearly savings percentage.
     */
    public function yearlySavingsPercentage(): float
    {
        if ($this->price_monthly == 0) {
            return 0;
        }

        $yearlyFromMonthly = $this->price_monthly * 12;
        if ($yearlyFromMonthly == 0) {
            return 0;
        }

        return (($yearlyFromMonthly - $this->price_yearly) / $yearlyFromMonthly) * 100;
    }
}
