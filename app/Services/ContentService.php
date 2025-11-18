<?php

namespace App\Services;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ContentRepositoryInterface;
use App\Contracts\Repositories\VideoAssetRepositoryInterface;
use App\DTOs\CreateContentDTO;
use App\DTOs\UpdateContentDTO;
use App\DTOs\ContentSearchDTO;
use App\Models\ContentItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ContentService
{
    public function __construct(
        private ContentRepositoryInterface $contentRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private VideoAssetRepositoryInterface $videoAssetRepository,
    ) {
    }

    /**
     * Create new content item.
     */
    public function createContent(CreateContentDTO $dto): ContentItem
    {
        return DB::transaction(function () use ($dto) {
            return $this->contentRepository->create($dto->toArray());
        });
    }

    /**
     * Update content item.
     */
    public function updateContent(int $contentId, UpdateContentDTO $dto): bool
    {
        return $this->contentRepository->update($contentId, $dto->toArray());
    }

    /**
     * Delete content item.
     */
    public function deleteContent(int $contentId): bool
    {
        return $this->contentRepository->delete($contentId);
    }

    /**
     * Publish content item.
     */
    public function publishContent(int $contentId): bool
    {
        $content = $this->contentRepository->find($contentId);
        
        if (!$content) {
            return false;
        }

        return $content->update([
            'published_at' => now(),
            'visibility' => 'public',
        ]);
    }

    /**
     * Unpublish content item.
     */
    public function unpublishContent(int $contentId): bool
    {
        $content = $this->contentRepository->find($contentId);
        
        if (!$content) {
            return false;
        }

        return $content->update([
            'published_at' => null,
            'visibility' => 'private',
        ]);
    }

    /**
     * Get content catalog with pagination.
     */
    public function getContentCatalog(int $perPage = 15): LengthAwarePaginator
    {
        return $this->contentRepository->getPublished($perPage);
    }

    /**
     * Search content.
     */
    public function searchContent(ContentSearchDTO $dto): LengthAwarePaginator
    {
        return $this->contentRepository->search($dto->query, $dto->toFiltersArray());
    }

    /**
     * Get content by category.
     */
    public function getContentByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->contentRepository->getByCategory($categoryId, $perPage);
    }

    /**
     * Get content by type.
     */
    public function getContentByType(string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->contentRepository->getByType($type, $perPage);
    }

    /**
     * Get content details with relations.
     */
    public function getContentDetails(string|int $contentId): ?ContentItem
    {
        $content = $this->contentRepository->getWithRelations($contentId);

        if ($content) {
            // Increment view count
            $this->contentRepository->incrementViews($contentId);
        }

        return $content;
    }

    /**
     * Get most viewed content.
     */
    public function getMostViewed(int $limit = 10): Collection
    {
        return $this->contentRepository->getMostViewed($limit);
    }

    /**
     * Get recently added content.
     */
    public function getRecentlyAdded(int $limit = 20): Collection
    {
        return $this->contentRepository->getRecentlyAdded($limit);
    }

    /**
     * Get content by provider.
     */
    public function getContentByProvider(int $providerId): Collection
    {
        return $this->contentRepository->getByProvider($providerId);
    }

    /**
     * Update content visibility.
     */
    public function updateVisibility(int $contentId, string $visibility): bool
    {
        return $this->contentRepository->updateVisibility($contentId, $visibility);
    }

    /**
     * Get all categories.
     */
    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->getActiveCategories();
    }

    /**
     * Get category by slug.
     */
    public function getCategoryBySlug(string $slug)
    {
        return $this->categoryRepository->findBySlug($slug);
    }

    /**
     * Get video assets for content.
     */
    public function getVideoAssets(int $contentId): Collection
    {
        return $this->videoAssetRepository->getByContentItem($contentId);
    }

    /**
     * Get HLS streaming assets.
     */
    public function getHlsAssets(int $contentId): Collection
    {
        return $this->videoAssetRepository->getHlsAssets($contentId);
    }

    /**
     * Get trending content based on recent views.
     */
    public function getTrending(int $limit = 20): Collection
    {
        return ContentItem::query()
            ->where('published_at', '<=', now())
            ->where('visibility', 'public')
            ->with(['category'])
            ->withCount(['watchHistory' => function ($query) {
                // Count views from last 7 days
                $query->where('created_at', '>=', now()->subDays(7));
            }])
            ->orderByDesc('watch_history_count')
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get personalized recommendations for user.
     */
    public function getRecommendations($user, int $limit = 20): Collection
    {
        // Get user's watch history to understand preferences
        $watchedCategories = DB::table('watch_history')
            ->join('content_items', 'watch_history.content_item_id', '=', 'content_items.id')
            ->where('watch_history.user_id', $user->id)
            ->where('watch_history.created_at', '>=', now()->subDays(30))
            ->pluck('content_items.category_id')
            ->unique()
            ->take(5);

        // Get content from same categories user has watched
        return ContentItem::query()
            ->where('published_at', '<=', now())
            ->where('visibility', 'public')
            ->when($watchedCategories->isNotEmpty(), function ($query) use ($watchedCategories) {
                $query->whereIn('category_id', $watchedCategories);
            })
            ->whereNotIn('id', function ($query) use ($user) {
                // Exclude already watched content
                $query->select('content_item_id')
                    ->from('watch_history')
                    ->where('user_id', $user->id);
            })
            ->with(['category'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Get content by genre/category slug.
     */
    public function getContentByGenre(string $slug, int $perPage = 20): LengthAwarePaginator
    {
        $category = $this->categoryRepository->findBySlug($slug);

        if (!$category) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return $this->contentRepository->getByCategory($category->id, $perPage);
    }

    /**
     * Get similar content based on category and tags.
     */
    public function getSimilarContent(int $contentId, int $limit = 10): Collection
    {
        $content = $this->contentRepository->find($contentId);

        if (!$content) {
            return new Collection();
        }

        return ContentItem::query()
            ->where('id', '!=', $contentId)
            ->where('published_at', '<=', now())
            ->where('visibility', 'public')
            ->where(function ($query) use ($content) {
                $query->where('category_id', $content->category_id)
                    ->orWhere('type', $content->type);
            })
            ->with(['category'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Get featured/hero content for homepage.
     */
    public function getFeaturedContent(): Collection
    {
        return ContentItem::query()
            ->where('published_at', '<=', now())
            ->where('visibility', 'public')
            ->where('featured', true)
            ->with(['category'])
            ->orderByDesc('featured_order')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();
    }
}
