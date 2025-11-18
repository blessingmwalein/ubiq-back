<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProfileRepositoryInterface;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Collection;

class EloquentProfileRepository extends EloquentRepository implements ProfileRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Profile $model)
    {
        $this->model = $model;
    }

    /**
     * Get profiles by account.
     */
    public function getByAccountId(int $accountId): Collection
    {
        return $this->model->where('account_id', $accountId)->get();
    }

    /**
     * Get primary profile for account.
     */
    public function getPrimaryProfile(int $accountId): ?Profile
    {
        return $this->model
            ->where('account_id', $accountId)
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Update profile interests.
     */
    public function updateInterests(int $profileId, array $interestIds): bool
    {
        $profile = $this->find($profileId);
        
        if (!$profile) {
            return false;
        }

        $profile->interests()->sync($interestIds);
        
        return true;
    }

    /**
     * Get profile with watch history.
     */
    public function getWithWatchHistory(int $profileId): ?Profile
    {
        return $this->model
            ->with('watchHistory.contentItem')
            ->find($profileId);
    }

    /**
     * Get profile with favorites.
     */
    public function getWithFavorites(int $profileId): ?Profile
    {
        return $this->model
            ->with('favorites.contentItem')
            ->find($profileId);
    }

    /**
     * Count profiles by account.
     */
    public function countByAccount(int $accountId): int
    {
        return $this->model->where('account_id', $accountId)->count();
    }
}
