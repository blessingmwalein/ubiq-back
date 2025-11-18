<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use App\Repositories\Eloquent\EloquentAccountRepository;
use App\Repositories\Eloquent\EloquentProfileRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProfileService
{
    public function __construct(
        private EloquentProfileRepository $profileRepository,
        private EloquentAccountRepository $accountRepository
    ) {
    }

    /**
     * Get all profiles for authenticated user's account.
     */
    public function getUserProfiles(User $user): Collection
    {
        $account = $this->accountRepository->getPrimaryAccount($user->id);
        
        if (!$account) {
            throw new Exception('No account found for user');
        }

        return $this->profileRepository->getByAccountId($account->id);
    }

    /**
     * Create a new profile.
     */
    public function createProfile(User $user, array $data): Profile
    {
        return DB::transaction(function () use ($user, $data) {
            $account = $this->accountRepository->getPrimaryAccount($user->id);
            
            if (!$account) {
                throw new Exception('No account found for user');
            }

            // Check profile limit from subscription
            $subscription = $account->subscriptions()->where('status', 'active')->latest()->first();
            $maxProfiles = $subscription?->package?->max_profiles ?? 5;
            
            $currentProfileCount = $this->profileRepository->countByAccount($account->id);
            
            if ($currentProfileCount >= $maxProfiles) {
                throw new Exception("Profile limit reached. Maximum {$maxProfiles} profiles allowed.");
            }

            // Prepare profile data
            $profileData = [
                'account_id' => $account->id,
                'name' => $data['name'],
                'avatar_url' => $data['avatar_url'] ?? null,
                'maturity_rating' => $data['maturity_rating'] ?? 'all',
                'is_kids' => $data['is_kids'] ?? false,
                'is_primary' => false,
            ];

            // Create profile
            $profile = $this->profileRepository->create($profileData);

            // Set PIN if provided
            if (!empty($data['pin'])) {
                $profile->setPin($data['pin']);
                $profile->save();
            }

            // Sync interests if provided
            if (!empty($data['interests'])) {
                $this->profileRepository->updateInterests($profile->id, $data['interests']);
            }

            return $profile->load('interests');
        });
    }

    /**
     * Update an existing profile.
     */
    public function updateProfile(User $user, string $profileUuid, array $data): Profile
    {
        return DB::transaction(function () use ($user, $profileUuid, $data) {
            $profile = $this->profileRepository->findByUuid($profileUuid);
            
            if (!$profile) {
                throw new Exception('Profile not found');
            }

            // Verify profile belongs to user's account
            $account = $this->accountRepository->getPrimaryAccount($user->id);
            
            if (!$account || $profile->account_id !== $account->id) {
                throw new Exception('Unauthorized to update this profile');
            }

            // Prevent primary profile changes
            if ($profile->is_primary) {
                unset($data['is_primary']);
            }

            // Update basic fields
            $updateData = [];
            foreach (['name', 'avatar_url', 'maturity_rating', 'is_kids'] as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (!empty($updateData)) {
                $this->profileRepository->update($profile->id, $updateData);
            }

            // Update PIN if provided
            if (isset($data['pin'])) {
                $profile->setPin($data['pin']);
                $profile->save();
            }

            // Update interests if provided
            if (isset($data['interests'])) {
                $this->profileRepository->updateInterests($profile->id, $data['interests']);
            }

            return $profile->fresh()->load('interests');
        });
    }

    /**
     * Delete a profile.
     */
    public function deleteProfile(User $user, string $profileUuid): bool
    {
        return DB::transaction(function () use ($user, $profileUuid) {
            $profile = $this->profileRepository->findByUuid($profileUuid);
            
            if (!$profile) {
                throw new Exception('Profile not found');
            }

            // Verify profile belongs to user's account
            $account = $this->accountRepository->getPrimaryAccount($user->id);
            
            if (!$account || $profile->account_id !== $account->id) {
                throw new Exception('Unauthorized to delete this profile');
            }

            // Prevent deletion of primary profile
            if ($profile->is_primary) {
                throw new Exception('Cannot delete primary profile');
            }

            return $this->profileRepository->delete($profile->id);
        });
    }

    /**
     * Switch to a profile (with PIN validation if required).
     */
    public function switchProfile(User $user, string $profileUuid, ?string $pin = null): Profile
    {
        $profile = $this->profileRepository->findByUuid($profileUuid);
        
        if (!$profile) {
            throw new Exception('Profile not found');
        }

        // Verify profile belongs to user's account
        $account = $this->accountRepository->getPrimaryAccount($user->id);
        
        if (!$account || $profile->account_id !== $account->id) {
            throw new Exception('Unauthorized to access this profile');
        }

        // Verify PIN if required
        if (!$profile->verifyPin($pin)) {
            throw new Exception('Invalid PIN');
        }

        return $profile->load('interests');
    }

    /**
     * Get a specific profile by UUID.
     */
    public function getProfile(User $user, string $profileUuid): Profile
    {
        $profile = $this->profileRepository->findByUuid($profileUuid);
        
        if (!$profile) {
            throw new Exception('Profile not found');
        }

        // Verify profile belongs to user's account
        $account = $this->accountRepository->getPrimaryAccount($user->id);
        
        if (!$account || $profile->account_id !== $account->id) {
            throw new Exception('Unauthorized to access this profile');
        }

        return $profile->load('interests');
    }

    /**
     * Get profile statistics.
     */
    public function getProfileStatistics(User $user, string $profileUuid): array
    {
        $profile = $this->getProfile($user, $profileUuid);

        return [
            'total_watch_time' => $profile->watchHistory()->sum('watch_duration'),
            'total_watched' => $profile->watchHistory()->count(),
            'favorites_count' => $profile->favorites()->count(),
            'continue_watching_count' => $profile->continueWatching()->count(),
            'interests_count' => $profile->interests()->count(),
        ];
    }
}
