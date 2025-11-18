<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\AccountRepositoryInterface;
use App\Contracts\Repositories\ProfileRepositoryInterface;
use App\DTOs\CreateProfileDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private ProfileRepositoryInterface $profileRepository,
    ) {
    }

    /**
     * Get user's accounts.
     */
    public function index(Request $request): JsonResponse
    {
        $accounts = $this->accountRepository->getByUserId($request->user()->id);

        return response()->json([
            'data' => $accounts->load('profiles'),
        ]);
    }

    /**
     * Get account details.
     */
    public function show(int $id): JsonResponse
    {
        $account = $this->accountRepository->find($id);

        if (!$account) {
            return response()->json([
                'message' => 'Account not found',
            ], 404);
        }

        return response()->json([
            'data' => $account->load('profiles'),
        ]);
    }

    /**
     * Create a new profile for account.
     */
    public function createProfile(Request $request, int $accountId): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'avatar_url' => 'nullable|url',
            'maturity_rating' => 'required|in:G,PG,PG-13,R,NC-17',
            'interest_ids' => 'nullable|array',
            'interest_ids.*' => 'exists:interests,id',
        ]);

        $data = array_merge($request->all(), ['account_id' => $accountId]);
        $dto = CreateProfileDTO::fromRequest($data);

        $profile = $this->profileRepository->create($dto->toArray());

        // Attach interests if provided
        if (!empty($dto->interestIds)) {
            $this->profileRepository->updateInterests($profile->id, $dto->interestIds);
        }

        return response()->json([
            'message' => 'Profile created successfully',
            'data' => $profile->fresh('interests'),
        ], 201);
    }

    /**
     * Update profile.
     */
    public function updateProfile(Request $request, int $profileId): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'avatar_url' => 'nullable|url',
            'maturity_rating' => 'sometimes|in:G,PG,PG-13,R,NC-17',
            'interest_ids' => 'nullable|array',
            'interest_ids.*' => 'exists:interests,id',
        ]);

        $profile = $this->profileRepository->find($profileId);

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found',
            ], 404);
        }

        $this->profileRepository->update($profileId, $request->only([
            'name',
            'avatar_url',
            'maturity_rating',
        ]));

        // Update interests if provided
        if ($request->has('interest_ids')) {
            $this->profileRepository->updateInterests($profileId, $request->interest_ids);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $profile->fresh('interests'),
        ]);
    }

    /**
     * Delete profile.
     */
    public function deleteProfile(int $profileId): JsonResponse
    {
        $profile = $this->profileRepository->find($profileId);

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found',
            ], 404);
        }

        if ($profile->is_primary) {
            return response()->json([
                'message' => 'Cannot delete primary profile',
            ], 400);
        }

        $this->profileRepository->delete($profileId);

        return response()->json([
            'message' => 'Profile deleted successfully',
        ]);
    }
}
