<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function __construct(
        private DeviceService $deviceService
    ) {
    }

    /**
     * Register a new device.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string|max:255',
            'device_name' => 'required|string|max:255',
            'device_type' => 'required|in:mobile,tablet,desktop,tv,other',
            'os_name' => 'nullable|string|max:50',
            'os_version' => 'nullable|string|max:50',
            'app_version' => 'nullable|string|max:50',
            'browser_name' => 'nullable|string|max:50',
            'browser_version' => 'nullable|string|max:50',
            'ip_address' => 'nullable|ip',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            $device = $this->deviceService->registerDevice(
                $request->user(),
                $request->all()
            );

            return response()->json([
                'message' => 'Device registered successfully',
                'data' => [
                    'id' => $device->uuid,
                    'device_id' => $device->device_id,
                    'device_name' => $device->device_name,
                    'device_type' => $device->device_type,
                    'os_name' => $device->os_name,
                    'is_active' => $device->is_active,
                    'last_used_at' => $device->last_used_at?->toIso8601String(),
                    'registered_at' => $device->registered_at?->toIso8601String(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get all user devices.
     */
    public function index(Request $request): JsonResponse
    {
        $activeOnly = $request->query('active_only', false);
        
        $devices = $this->deviceService->getUserDevices(
            $request->user(),
            $activeOnly
        );

        return response()->json([
            'data' => $devices->map(function ($device) {
                return [
                    'id' => $device->uuid,
                    'device_id' => $device->device_id,
                    'device_name' => $device->device_name,
                    'device_type' => $device->device_type,
                    'os_name' => $device->os_name,
                    'os_version' => $device->os_version,
                    'app_version' => $device->app_version,
                    'browser_name' => $device->browser_name,
                    'ip_address' => $device->ip_address,
                    'location' => $device->location,
                    'is_active' => $device->is_active,
                    'last_used_at' => $device->last_used_at?->toIso8601String(),
                    'registered_at' => $device->registered_at?->toIso8601String(),
                ];
            })->values(),
        ]);
    }

    /**
     * Remove a device.
     */
    public function destroy(Request $request, string $deviceUuid): JsonResponse
    {
        try {
            $this->deviceService->removeDevice($request->user(), $deviceUuid);

            return response()->json([
                'message' => 'Device removed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Verify device session.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string',
        ]);

        $isValid = $this->deviceService->verifyDevice(
            $request->user(),
            $request->input('device_id')
        );

        return response()->json([
            'data' => [
                'valid' => $isValid,
                'message' => $isValid ? 'Device is active' : 'Device is not active or not found',
            ],
        ]);
    }

    /**
     * Logout from device.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string',
        ]);

        $success = $this->deviceService->logoutDevice(
            $request->user(),
            $request->input('device_id')
        );

        return response()->json([
            'message' => $success ? 'Logged out from device successfully' : 'Device not found',
        ]);
    }

    /**
     * Logout from all devices.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->deviceService->logoutAllDevices($request->user());

        return response()->json([
            'message' => 'Logged out from all devices successfully',
        ]);
    }

    /**
     * Get device statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $stats = $this->deviceService->getDeviceStatistics($request->user());

        return response()->json([
            'data' => $stats,
        ]);
    }
}
