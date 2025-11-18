<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'device_id',
        'device_name',
        'device_type',
        'os_name',
        'os_version',
        'app_version',
        'browser_name',
        'browser_version',
        'ip_address',
        'location',
        'is_active',
        'last_used_at',
        'registered_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'registered_at' => 'datetime',
    ];

    /**
     * Get the unique identifier column name.
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the user that owns the device.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update last used timestamp.
     */
    public function updateLastUsed(): void
    {
        $this->update([
            'last_used_at' => now(),
            'is_active' => true,
        ]);
    }

    /**
     * Deactivate device.
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Check if device is currently active.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }
}
