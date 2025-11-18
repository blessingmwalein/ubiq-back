<?php

namespace App\Contracts\Repositories;

use App\Models\ContentItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ContentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get published content.
     */
    public function getPublished(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get content by category.
     */
    public function getByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get content by type.
     */
    public function getByType(string $type, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get content by provider.
     */
    public function getByProvider(int $providerId): Collection;

    /**
     * Search content.
     */
    public function search(string $query, array $filters = []): LengthAwarePaginator;

    /**
     * Get most viewed content.
     */
    public function getMostViewed(int $limit = 10, ?int $categoryId = null): Collection;

    /**
     * Get recently added content.
     */
    public function getRecentlyAdded(int $limit = 10): Collection;

    /**
     * Increment views count.
     */
    public function incrementViews(string|int $contentId): bool;

    /**
     * Get content with relations.
     */
    public function getWithRelations(string|int $contentId, array $relations = []): ?ContentItem;

    /**
     * Update visibility.
     */
    public function updateVisibility(string|int $contentId, string $visibility): bool;
}
