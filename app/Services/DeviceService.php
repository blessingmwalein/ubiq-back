<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use App\Repositories\Eloquent\EloquentDeviceRepository;
use Illuminate\Support\Collection;

class DeviceService
{
    public function __construct(
        private EloquentDeviceRepository $deviceRepository
    ) {
    }

    /**
     * Register a new device for user.
     */
    public function registerDevice(User $user, array $data): UserDevice
    {
        // Validate device limit (e.g., max 5 devices per user)
        $activeDevices = $this->deviceRepository->getActiveDeviceCount($user->id);
        
        if ($activeDevices >= 10) {
            throw new \Exception('Maximum device limit reached. Please remove a device before adding a new one.');
        }

        return $this->deviceRepository->register($user->id, $data);
    }

    /**
     * Get all devices for user.
     */
    public function getUserDevices(User $user, bool $activeOnly = false): Collection
    {
        return $this->deviceRepository->getUserDevices($user->id, $activeOnly);
    }

    /**
     * Get device by UUID.
     */
    public function getDeviceByUuid(string $uuid): ?UserDevice
    {
        return $this->deviceRepository->findByUuid($uuid);
    }

    /**
     * Update device activity.
     */
    public function updateDeviceActivity(User $user, string $deviceId): void
    {
        $device = $this->deviceRepository->findByDeviceId($user->id, $deviceId);
        
        if ($device) {
            $this->deviceRepository->updateLastUsed($device->id);
        }
    }

    /**
     * Remove device by UUID.
     */
    public function removeDevice(User $user, string $deviceUuid): bool
    {
        $device = $this->deviceRepository->findByUuid($deviceUuid);
        
        if (!$device || $device->user_id !== $user->id) {
            throw new \Exception('Device not found or does not belong to user');
        }

        return $this->deviceRepository->remove($device->id);
    }

    /**
     * Deactivate device.
     */
    public function deactivateDevice(User $user, string $deviceUuid): bool
    {
        $device = $this->deviceRepository->findByUuid($deviceUuid);
        
        if (!$device || $device->user_id !== $user->id) {
            throw new \Exception('Device not found or does not belong to user');
        }

        return $this->deviceRepository->deactivate($device->id);
    }

    /**
     * Verify device belongs to user and is active.
     */
    public function verifyDevice(User $user, string $deviceId): bool
    {
        $device = $this->deviceRepository->findByDeviceId($user->id, $deviceId);
        
        return $device && $device->isActive();
    }

    /**
     * Logout from device (deactivate).
     */
    public function logoutDevice(User $user, string $deviceId): bool
    {
        $device = $this->deviceRepository->findByDeviceId($user->id, $deviceId);
        
        if (!$device) {
            return false;
        }

        return $this->deviceRepository->deactivate($device->id);
    }

    /**
     * Logout from all devices.
     */
    public function logoutAllDevices(User $user): bool
    {
        return $this->deviceRepository->deactivateAllUserDevices($user->id);
    }

    /**
     * Get device statistics for user.
     */
    public function getDeviceStatistics(User $user): array
    {
        $devices = $this->getUserDevices($user);
        
        return [
            'total_devices' => $devices->count(),
            'active_devices' => $devices->where('is_active', true)->count(),
            'device_types' => $devices->groupBy('device_type')->map->count()->toArray(),
            'most_recent' => $devices->sortByDesc('last_used_at')->first(),
        ];
    }
}
