<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateUserDTO;
use App\DTOs\LoginDTO;
use App\DTOs\SocialLoginDTO;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $dto = CreateUserDTO::fromRequest($request->all());
        $result = $this->authService->register($dto);

        return response()->json([
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => $result['user']->id,
                    'name' => $result['user']->name,
                    'email' => $result['user']->email,
                    'avatar_url' => $result['user']->avatar_url,
                    'onboarding_completed' => $result['user']->onboarding_completed,
                ],
                'account' => [
                    'id' => $result['account']->uuid,
                    'status' => $result['account']->status,
                    'subscription' => $result['account']->subscriptions()->latest()->first(),
                ],
                'token' => $result['token'],
            ],
        ], 201);
    }

    /**
     * Login with email and password.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            // Optional device tracking fields
            'device_id' => 'nullable|string',
            'device_name' => 'nullable|string',
            'device_type' => 'nullable|string|in:mobile,tablet,desktop,tv,other',
            'os_name' => 'nullable|string',
            'os_version' => 'nullable|string',
            'app_version' => 'nullable|string',
            'browser_name' => 'nullable|string',
            'browser_version' => 'nullable|string',
            'ip_address' => 'nullable|ip',
            'location' => 'nullable|string',
        ]);

        $dto = LoginDTO::fromRequest($request->all());
        
        // Extract device data if provided
        $deviceData = null;
        if ($request->has('device_id')) {
            $deviceData = [
                'device_id' => $request->input('device_id'),
                'device_name' => $request->input('device_name', 'Unknown Device'),
                'device_type' => $request->input('device_type', 'other'),
                'os_name' => $request->input('os_name'),
                'os_version' => $request->input('os_version'),
                'app_version' => $request->input('app_version'),
                'browser_name' => $request->input('browser_name'),
                'browser_version' => $request->input('browser_version'),
                'ip_address' => $request->input('ip_address', $request->ip()),
                'location' => $request->input('location'),
            ];
        }

        $result = $this->authService->login($dto, $deviceData);

        return response()->json([
            'message' => 'Login successful',
            'data' => $result,
        ]);
    }

    /**
     * Login with social provider.
     */
    public function socialLogin(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|string|in:google,facebook,apple',
            'provider_id' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string',
            'avatar_url' => 'nullable|url',
            // Optional device tracking fields
            'device_id' => 'nullable|string',
            'device_name' => 'nullable|string',
            'device_type' => 'nullable|string|in:mobile,tablet,desktop,tv,other',
            'os_name' => 'nullable|string',
            'os_version' => 'nullable|string',
            'app_version' => 'nullable|string',
            'browser_name' => 'nullable|string',
            'browser_version' => 'nullable|string',
            'ip_address' => 'nullable|ip',
            'location' => 'nullable|string',
        ]);

        $dto = SocialLoginDTO::fromRequest($request->all());
        
        // Extract device data if provided
        $deviceData = null;
        if ($request->has('device_id')) {
            $deviceData = [
                'device_id' => $request->input('device_id'),
                'device_name' => $request->input('device_name', 'Unknown Device'),
                'device_type' => $request->input('device_type', 'other'),
                'os_name' => $request->input('os_name'),
                'os_version' => $request->input('os_version'),
                'app_version' => $request->input('app_version'),
                'browser_name' => $request->input('browser_name'),
                'browser_version' => $request->input('browser_version'),
                'ip_address' => $request->input('ip_address', $request->ip()),
                'location' => $request->input('location'),
            ];
        }
        
        $result = $this->authService->loginWithSocial($dto, $deviceData);

        return response()->json([
            'message' => 'Social login successful',
            'data' => $result,
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $result = $this->authService->me($request->user());

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
     * Send password reset link.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $message = $this->authService->sendResetLink($request->email);

        return response()->json([
            'message' => $message,
        ]);
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $message = $this->authService->resetPassword(
            $request->email,
            $request->password,
            $request->token
        );

        return response()->json([
            'message' => $message,
        ]);
    }
}
