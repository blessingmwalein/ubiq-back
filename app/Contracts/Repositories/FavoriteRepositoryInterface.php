<?php

namespace App\Contracts\Repositories;

use App\Models\Favorite;
use Illuminate\Database\Eloquent\Collection;

interface FavoriteRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get favorites for profile.
     */
    public function getByProfile(int $profileId): Collection;

    /**
     * Check if content is favorited.
     */
    public function isFavorited(int $profileId, int $contentItemId): bool;

    /**
     * Add to favorites.
     */
    public function addFavorite(int $profileId, int $contentItemId): Favorite;

    /**
     * Remove from favorites.
     */
    public function removeFavorite(int $profileId, int $contentItemId): bool;

    /**
     * Toggle favorite.
     */
    public function toggleFavorite(int $profileId, int $contentItemId): bool;

    /**
     * Get favorite count for content.
     */
    public function countForContent(int $contentItemId): int;

    /**
     * Get most favorited content.
     */
    public function getMostFavorited(int $limit = 20): Collection;
}
