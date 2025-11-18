<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\FavoriteRepositoryInterface;
use App\Models\Favorite;
use Illuminate\Database\Eloquent\Collection;

class EloquentFavoriteRepository extends EloquentRepository implements FavoriteRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Favorite $model)
    {
        $this->model = $model;
    }

    /**
     * Get favorites for profile.
     */
    public function getByProfile(int $profileId): Collection
    {
        return $this->model
            ->where('profile_id', $profileId)
            ->with('contentItem')
            ->latest()
            ->get();
    }

    /**
     * Check if content is favorited.
     */
    public function isFavorited(int $profileId, int $contentItemId): bool
    {
        return $this->model
            ->where('profile_id', $profileId)
            ->where('content_item_id', $contentItemId)
            ->exists();
    }

    /**
     * Add to favorites.
     */
    public function addFavorite(int $profileId, int $contentItemId): Favorite
    {
        return $this->model->firstOrCreate([
            'profile_id' => $profileId,
            'content_item_id' => $contentItemId,
        ]);
    }

    /**
     * Remove from favorites.
     */
    public function removeFavorite(int $profileId, int $contentItemId): bool
    {
        return $this->model
            ->where('profile_id', $profileId)
            ->where('content_item_id', $contentItemId)
            ->delete() > 0;
    }

    /**
     * Toggle favorite.
     */
    public function toggleFavorite(int $profileId, int $contentItemId): bool
    {
        if ($this->isFavorited($profileId, $contentItemId)) {
            $this->removeFavorite($profileId, $contentItemId);
            return false;
        }

        $this->addFavorite($profileId, $contentItemId);
        return true;
    }

    /**
     * Get favorite count for content.
     */
    public function countForContent(int $contentItemId): int
    {
        return $this->model->where('content_item_id', $contentItemId)->count();
    }

    /**
     * Get most favorited content.
     */
    public function getMostFavorited(int $limit = 20): Collection
    {
        return $this->model
            ->select('content_item_id')
            ->selectRaw('COUNT(*) as favorites_count')
            ->with('contentItem')
            ->groupBy('content_item_id')
            ->orderByDesc('favorites_count')
            ->limit($limit)
            ->get();
    }
}
