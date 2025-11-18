<?php

namespace App\Repositories\Eloquent;

use App\Models\UserDevice;
use Illuminate\Support\Collection;

class EloquentDeviceRepository
{
    /**
     * Register a new device for user.
     */
    public function register(int $userId, array $data): UserDevice
    {
        // Check if device already exists
        $existingDevice = UserDevice::where('user_id', $userId)
            ->where('device_id', $data['device_id'])
            ->first();

        if ($existingDevice) {
            // Update existing device
            $existingDevice->update([
                'device_name' => $data['device_name'] ?? $existingDevice->device_name,
                'device_type' => $data['device_type'] ?? $existingDevice->device_type,
                'os_name' => $data['os_name'] ?? $existingDevice->os_name,
                'os_version' => $data['os_version'] ?? $existingDevice->os_version,
                'app_version' => $data['app_version'] ?? $existingDevice->app_version,
                'browser_name' => $data['browser_name'] ?? $existingDevice->browser_name,
                'browser_version' => $data['browser_version'] ?? $existingDevice->browser_version,
                'ip_address' => $data['ip_address'] ?? $existingDevice->ip_address,
                'location' => $data['location'] ?? $existingDevice->location,
                'is_active' => true,
                'last_used_at' => now(),
            ]);

            return $existingDevice;
        }

        // Create new device
        return UserDevice::create([
            'user_id' => $userId,
            'device_id' => $data['device_id'],
            'device_name' => $data['device_name'] ?? 'Unknown Device',
            'device_type' => $data['device_type'] ?? 'other',
            'os_name' => $data['os_name'] ?? null,
            'os_version' => $data['os_version'] ?? null,
            'app_version' => $data['app_version'] ?? null,
            'browser_name' => $data['browser_name'] ?? null,
            'browser_version' => $data['browser_version'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'location' => $data['location'] ?? null,
            'is_active' => true,
            'registered_at' => now(),
            'last_used_at' => now(),
        ]);
    }

    /**
     * Get all devices for a user.
     */
    public function getUserDevices(int $userId, bool $activeOnly = false): Collection
    {
        $query = UserDevice::where('user_id', $userId);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('last_used_at', 'desc')->get();
    }

    /**
     * Find device by ID.
     */
    public function findById(int $id): ?UserDevice
    {
        return UserDevice::find($id);
    }

    /**
     * Find device by UUID.
     */
    public function findByUuid(string $uuid): ?UserDevice
    {
        return UserDevice::where('uuid', $uuid)->first();
    }

    /**
     * Find device by device ID and user.
     */
    public function findByDeviceId(int $userId, string $deviceId): ?UserDevice
    {
        return UserDevice::where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->first();
    }

    /**
     * Update device last used timestamp.
     */
    public function updateLastUsed(int $id): bool
    {
        $device = $this->findById($id);
        
        if ($device) {
            $device->updateLastUsed();
            return true;
        }

        return false;
    }

    /**
     * Deactivate device.
     */
    public function deactivate(int $id): bool
    {
        $device = $this->findById($id);
        
        if ($device) {
            $device->deactivate();
            return true;
        }

        return false;
    }

    /**
     * Remove device.
     */
    public function remove(int $id): bool
    {
        $device = $this->findById($id);
        
        if ($device) {
            return $device->delete();
        }

        return false;
    }

    /**
     * Get active device count for user.
     */
    public function getActiveDeviceCount(int $userId): int
    {
        return UserDevice::where('user_id', $userId)
            ->where('is_active', true)
            ->count();
    }

    /**
     * Deactivate all devices for user.
     */
    public function deactivateAllUserDevices(int $userId): bool
    {
        return UserDevice::where('user_id', $userId)
            ->update(['is_active' => false]);
    }
}
