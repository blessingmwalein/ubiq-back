<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ContentRepositoryInterface;
use App\Models\ContentItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentContentRepository extends EloquentRepository implements ContentRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(ContentItem $model)
    {
        $this->model = $model;
    }

    /**
     * Get published content.
     */
    public function getPublished(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->published()->paginate($perPage);
    }

    /**
     * Get content by category.
     */
    public function getByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->published()
            ->inCategory($categoryId)
            ->paginate($perPage);
    }

    /**
     * Get content by type.
     */
    public function getByType(string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->ofType($type)->published()->paginate($perPage);
    }

    /**
     * Get content by provider.
     */
    public function getByProvider(int $providerId): Collection
    {
        return $this->model
            ->where('content_provider_id', $providerId)
            ->published()
            ->get();
    }

    /**
     * Search content.
     */
    public function search(string $query, array $filters = []): LengthAwarePaginator
    {
        $queryBuilder = $this->model->query();

        // Search in title, description, and genre if query is provided
        if (!empty($query)) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('genre', 'LIKE', "%{$query}%");
            });
        }

        // Only show published content by default
        $queryBuilder->published();

        // Apply category filter
        if (isset($filters['category_id'])) {
            $queryBuilder->where('category_id', $filters['category_id']);
        }

        // Apply type filter
        if (isset($filters['type'])) {
            $queryBuilder->where('type', $filters['type']);
        }

        // Apply maturity rating filter
        if (isset($filters['maturity_rating'])) {
            $queryBuilder->where('maturity_rating', $filters['maturity_rating']);
        }

        // Apply release year filter
        if (isset($filters['release_year'])) {
            $queryBuilder->where('release_year', $filters['release_year']);
        }

        // Apply genre filter (supports partial match for comma-separated genres)
        if (isset($filters['genre'])) {
            $queryBuilder->where('genre', 'LIKE', "%{$filters['genre']}%");
        }

        // Apply visibility filter
        if (isset($filters['visibility'])) {
            $queryBuilder->where('visibility', $filters['visibility']);
        }

        // Apply provider filter
        if (isset($filters['provider_id'])) {
            $queryBuilder->where('provider_id', $filters['provider_id']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $queryBuilder->with(['category', 'provider'])->paginate($perPage);
    }

    /**
     * Get most viewed content.
     */
    public function getMostViewed(int $limit = 10, ?int $categoryId = null): Collection
    {
        $query = $this->model->published()->orderBy('view_count', 'desc');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get recently added content.
     */
    public function getRecentlyAdded(int $limit = 10): Collection
    {
        return $this->model
            ->published()
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Increment view count.
     */
    public function incrementViews(string|int $contentItemId): bool
    {
        $content = $this->find($contentItemId);
        
        if (!$content) {
            return false;
        }

        return $content->update(['view_count' => $content->view_count + 1]);
    }

    /**
     * Get content with relations.
     */
    public function getWithRelations(string|int $contentItemId, array $relations = []): ?ContentItem
    {
        $defaultRelations = ['category', 'provider', 'show.seasons.episodes', 'videoAssets'];
        $loadRelations = !empty($relations) ? $relations : $defaultRelations;

        return $this->model
            ->with($loadRelations)
            ->find($contentItemId);
    }

    /**
     * Update visibility.
     */
    public function updateVisibility(string|int $contentItemId, string $visibility): bool
    {
        return $this->update($contentItemId, ['visibility' => $visibility]);
    }
}
