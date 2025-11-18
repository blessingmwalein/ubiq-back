<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OnboardingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function __construct(
        private OnboardingService $onboardingService
    ) {}

    /**
     * Get available interests for selection.
     */
    public function getInterests(): JsonResponse
    {
        $interests = $this->onboardingService->getAvailableInterests();

        return response()->json([
            'data' => $interests,
        ]);
    }

    /**
     * Complete user onboarding.
     */
    public function completeOnboarding(Request $request): JsonResponse
    {
        $request->validate([
            'avatar_url' => 'nullable|url|max:500',
            'date_of_birth' => 'nullable|date|before:today',
            'phone_number' => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:10',
            'interests' => 'nullable|array',
            'interests.*' => 'integer|exists:interests,id',
        ]);

        $user = $request->user();

        $updatedUser = $this->onboardingService->completeOnboarding(
            $user,
            $request->only(['avatar_url', 'date_of_birth', 'phone_number', 'country_code', 'interests'])
        );

        return response()->json([
            'message' => 'Onboarding completed successfully',
            'data' => [
                'user' => [
                    'id' => $updatedUser->id,
                    'name' => $updatedUser->name,
                    'email' => $updatedUser->email,
                    'avatar_url' => $updatedUser->avatar_url,
                    'date_of_birth' => $updatedUser->date_of_birth?->format('Y-m-d'),
                    'phone_number' => $updatedUser->phone_number,
                    'onboarding_completed' => $updatedUser->onboarding_completed,
                ],
                'account' => [
                    'id' => $updatedUser->account->uuid,
                    'subscription_status' => $updatedUser->account->subscriptions()->latest()->first()?->status ?? 'inactive',
                ],
            ],
        ]);
    }

    /**
     * Update user interests.
     */
    public function updateInterests(Request $request): JsonResponse
    {
        $request->validate([
            'interests' => 'required|array|min:1',
            'interests.*' => 'string|exists:interests,slug',
        ]);

        $user = $request->user();

        $this->onboardingService->updateInterests($user, $request->input('interests'));

        return response()->json([
            'message' => 'Interests updated successfully',
            'data' => [
                'interests' => $request->input('interests'),
            ],
        ]);
    }

    /**
     * Get onboarding progress.
     */
    public function getProgress(Request $request): JsonResponse
    {
        $user = $request->user();

        $progress = $this->onboardingService->getOnboardingProgress($user);

        return response()->json([
            'data' => $progress,
        ]);
    }
}
