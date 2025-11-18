<?php

namespace App\Contracts\Repositories;

use App\Models\WatchHistory;
use Illuminate\Database\Eloquent\Collection;

interface WatchHistoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get watch history for profile.
     */
    public function getByProfile(int $profileId, int $limit = 50): Collection;

    /**
     * Get watch history for content item.
     */
    public function getByContentItem(int $contentItemId): Collection;

    /**
     * Get watch history for specific profile and content.
     */
    public function getForProfileAndContent(int $profileId, int $contentItemId): ?WatchHistory;

    /**
     * Find or create watch history entry.
     */
    public function findOrCreate(int $profileId, int $contentItemId): WatchHistory;

    /**
     * Update watch progress.
     */
    public function updateProgress(int $profileId, int $contentItemId, int $progress, int $duration): WatchHistory;

    /**
     * Mark as completed.
     */
    public function markAsCompleted(int $profileId, int $contentItemId): bool;

    /**
     * Get continue watching list.
     */
    public function getContinueWatching(int $profileId, int $limit = 10): Collection;

    /**
     * Get recently watched.
     */
    public function getRecentlyWatched(int $profileId, int $limit = 20): Collection;

    /**
     * Delete watch history for profile.
     */
    public function deleteForProfile(int $profileId): int;
}
