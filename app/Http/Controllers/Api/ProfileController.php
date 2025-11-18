<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {
    }

    /**
     * Get all profiles for authenticated user.
     * 
     * GET /api/profiles
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $profiles = $this->profileService->getUserProfiles($request->user());

            return response()->json([
                'data' => $profiles,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve profiles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new profile.
     * 
     * POST /api/profiles
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'avatar_url' => 'nullable|url',
            'maturity_rating' => 'nullable|string|in:all,pg,pg13,r,adult',
            'is_kids' => 'nullable|boolean',
            'pin' => 'nullable|string|digits:4',
            'interests' => 'nullable|array',
            'interests.*' => 'integer|exists:interests,id',
        ]);

        try {
            // Remove interests validation temporarily or ensure they exist
            $data = $request->all();
            
            // Only include interests if they're valid and exist
            if (isset($data['interests']) && is_array($data['interests'])) {
                // Filter out any invalid interest IDs
                $validInterests = \App\Models\Interest::whereIn('id', $data['interests'])->pluck('id')->toArray();
                $data['interests'] = $validInterests;
                
                // If no valid interests remain, remove the key
                if (empty($data['interests'])) {
                    unset($data['interests']);
                }
            }
            
            $profile = $this->profileService->createProfile(
                $request->user(),
                $data
            );

            return response()->json([
                'message' => 'Profile created successfully',
                'data' => $profile,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to create profile',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get a specific profile.
     * 
     * GET /api/profiles/{uuid}
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        try {
            $profile = $this->profileService->getProfile($request->user(), $uuid);

            return response()->json([
                'data' => $profile,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update a profile.
     * 
     * PUT /api/profiles/{uuid}
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'avatar_url' => 'nullable|url',
            'maturity_rating' => 'sometimes|string|in:all,pg,pg13,r,adult,teen',
            'is_kids' => 'sometimes|boolean',
            'pin' => 'nullable|string|digits:4',
            'interests' => 'nullable|array',
            'interests.*' => 'integer',
        ]);

        try {
            $data = $request->all();
            
            // Only include interests if they're valid and exist
            if (isset($data['interests']) && is_array($data['interests'])) {
                // Filter out any invalid interest IDs
                $validInterests = \App\Models\Interest::whereIn('id', $data['interests'])->pluck('id')->toArray();
                $data['interests'] = $validInterests;
                
                // If no valid interests remain, remove the key
                if (empty($data['interests'])) {
                    unset($data['interests']);
                }
            }
            
            $profile = $this->profileService->updateProfile(
                $request->user(),
                $uuid,
                $data
            );

            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => $profile,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete a profile.
     * 
     * DELETE /api/profiles/{uuid}
     */
    public function destroy(Request $request, string $uuid): JsonResponse
    {
        try {
            $this->profileService->deleteProfile($request->user(), $uuid);

            return response()->json([
                'message' => 'Profile deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete profile',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Switch to a profile (with PIN validation).
     * 
     * POST /api/profiles/{uuid}/switch
     */
    public function switch(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'pin' => 'nullable|string|digits:4',
        ]);

        try {
            $profile = $this->profileService->switchProfile(
                $request->user(),
                $uuid,
                $request->input('pin')
            );

            return response()->json([
                'message' => 'Profile switched successfully',
                'data' => $profile,
            ]);
        } catch (Exception $e) {
            $statusCode = $e->getMessage() === 'Invalid PIN' ? 403 : 400;
            
            return response()->json([
                'message' => 'Failed to switch profile',
                'error' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Get profile statistics.
     * 
     * GET /api/profiles/{uuid}/statistics
     */
    public function statistics(Request $request, string $uuid): JsonResponse
    {
        try {
            $statistics = $this->profileService->getProfileStatistics($request->user(), $uuid);

            return response()->json([
                'data' => $statistics,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
