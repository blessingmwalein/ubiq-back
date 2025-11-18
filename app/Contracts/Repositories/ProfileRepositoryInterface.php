<?php

namespace App\Contracts\Repositories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Collection;

interface ProfileRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get profiles by account ID.
     */
    public function getByAccountId(int $accountId): Collection;

    /**
     * Get primary profile for account.
     */
    public function getPrimaryProfile(int $accountId): ?Profile;

    /**
     * Update profile interests.
     */
    public function updateInterests(int $profileId, array $interestIds): bool;

    /**
     * Get profile with watch history.
     */
    public function getWithWatchHistory(int $profileId): ?Profile;

    /**
     * Get profile with favorites.
     */
    public function getWithFavorites(int $profileId): ?Profile;

    /**
     * Count profiles for account.
     */
    public function countByAccount(int $accountId): int;
}
