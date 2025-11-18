<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\WatchHistoryRepositoryInterface;
use App\Models\WatchHistory;
use Illuminate\Database\Eloquent\Collection;

class EloquentWatchHistoryRepository extends EloquentRepository implements WatchHistoryRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(WatchHistory $model)
    {
        $this->model = $model;
    }

    /**
     * Get watch history for profile.
     */
    public function getByProfile(int $profileId, int $limit = 50): Collection
    {
        return $this->model
            ->where('profile_id', $profileId)
            ->with('contentItem')
            ->latest('last_watched_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get watch history for content item.
     */
    public function getByContentItem(int $contentItemId): Collection
    {
        return $this->model
            ->where('content_item_id', $contentItemId)
            ->with('profile')
            ->get();
    }

    /**
     * Get watch history for specific profile and content.
     */
    public function getForProfileAndContent(int $profileId, int $contentItemId): ?WatchHistory
    {
        return $this->model
            ->where('profile_id', $profileId)
            ->where('content_item_id', $contentItemId)
            ->first();
    }

    /**
     * Find or create watch history entry.
     */
    public function findOrCreate(int $profileId, int $contentItemId): WatchHistory
    {
        return $this->model->firstOrCreate(
            [
                'profile_id' => $profileId,
                'content_item_id' => $contentItemId,
            ],
            [
                'progress_seconds' => 0,
                'duration_seconds' => 0,
                'completed' => false,
            ]
        );
    }

    /**
     * Update watch progress.
     */
    public function updateProgress(int $profileId, int $contentItemId, int $progress, int $duration): WatchHistory
    {
        $watchHistory = $this->findOrCreate($profileId, $contentItemId);

        $completed = ($progress >= $duration * 0.9); // Mark as completed if watched 90%

        $watchHistory->update([
            'progress_seconds' => $progress,
            'duration_seconds' => $duration,
            'completed' => $completed,
            'last_watched_at' => now(),
        ]);

        return $watchHistory->fresh();
    }

    /**
     * Mark as completed.
     */
    public function markAsCompleted(int $profileId, int $contentItemId): bool
    {
        $watchHistory = $this->findOrCreate($profileId, $contentItemId);

        return $watchHistory->update(['completed' => true]);
    }

    /**
     * Get continue watching list.
     */
    public function getContinueWatching(int $profileId, int $limit = 10): Collection
    {
        return $this->model
            ->where('profile_id', $profileId)
            ->where('completed', false)
            ->where('progress_seconds', '>', 0)
            ->with('contentItem')
            ->latest('last_watched_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recently watched.
     */
    public function getRecentlyWatched(int $profileId, int $limit = 20): Collection
    {
        return $this->model
            ->where('profile_id', $profileId)
            ->with('contentItem')
            ->latest('last_watched_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Delete watch history for profile.
     */
    public function deleteForProfile(int $profileId): int
    {
        return $this->model->where('profile_id', $profileId)->delete();
    }
}
