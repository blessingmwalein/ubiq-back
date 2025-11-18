<?php

namespace App\Services;

use App\Models\User;
use App\Models\Interest;
use App\Models\Profile;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\Eloquent\EloquentProfileRepository;
use Illuminate\Support\Facades\DB;

class OnboardingService
{
    public function __construct(
        private EloquentUserRepository $userRepository,
        private EloquentProfileRepository $profileRepository
    ) {}

    /**
     * Complete user onboarding with profile details.
     */
    public function completeOnboarding(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            // Update user basic information
            $userData = [];

            if (isset($data['avatar_url'])) {
                $userData['avatar_url'] = $data['avatar_url'];
            }

            if (isset($data['date_of_birth'])) {
                $userData['date_of_birth'] = $data['date_of_birth'];
            }

            if (isset($data['phone_number'])) {
                $userData['phone_number'] = $data['phone_number'];
            }

            if (isset($data['country_code'])) {
                $userData['country_code'] = $data['country_code'];
            }

            $userData['onboarding_completed'] = true;

            $user->update($userData);

            // Update primary profile if interests provided
            if (isset($data['interests']) && is_array($data['interests'])) {
                $profile = $user->account->profiles()->where('is_primary', true)->first();

                if ($profile) {

                    $this->profileRepository->updateInterests($profile->id, $data['interests']);

                    // $this->syncProfileInterests($profile, $data['interests']);
                }
            }

            return $user->fresh();
        });
    }

    /**
     * Get all available interests.
     */
    public function getAvailableInterests(): array
    {
        return Interest::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category.title')
            ->map(function ($interests) {
                return $interests->map(function ($interest) {
                    return [
                        'id' => $interest->id,
                        'uuid' => $interest->uuid,
                        'name' => $interest->name,
                        'slug' => $interest->slug,
                        'description' => $interest->description,
                    ];
                });
            })
            ->toArray();
    }

    /**
     * Update user interests during onboarding.
     */
    public function updateInterests(User $user, array $interestSlugs): void
    {
        $profile = $user->account->profiles()->where('is_primary', true)->first();

        if (!$profile) {
            throw new \Exception('No primary profile found for user');
        }

        $this->syncProfileInterests($profile, $interestSlugs);
    }

    /**
     * Sync profile interests.
     */
    private function syncProfileInterests(Profile $profile, array $interestSlugs): void
    {
        $interests = Interest::whereIn('slug', $interestSlugs)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        $profile->interests()->sync($interests);
    }

    /**
     * Check if user has completed onboarding.
     */
    public function hasCompletedOnboarding(User $user): bool
    {
        return $user->onboarding_completed === true;
    }

    /**
     * Get onboarding progress for user.
     */
    public function getOnboardingProgress(User $user): array
    {
        $profile = $user->account?->profiles()?->where('is_primary', true)->first();

        return [
            'completed' => $user->onboarding_completed,
            'steps' => [
                'basic_info' => [
                    'completed' => !empty($user->name) && !empty($user->email),
                    'required' => true,
                ],
                'profile_details' => [
                    'completed' => !empty($user->avatar_url) || !empty($user->date_of_birth),
                    'required' => false,
                ],
                'interests' => [
                    'completed' => $profile && $profile->interests()->count() > 0,
                    'required' => false,
                ],
                'subscription' => [
                    'completed' => $user->account && $user->account->subscriptions()->exists(),
                    'required' => true,
                ],
            ],
        ];
    }
}
