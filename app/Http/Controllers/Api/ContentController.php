<?php

namespace App\Http\Controllers\Api;

use App\DTOs\ContentSearchDTO;
use App\Http\Controllers\Controller;
use App\Services\ContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(
        private ContentService $contentService
    ) {}

    /**
     * Get content catalog.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);

        // For debugging: if 'all' parameter is set, don't filter by published
        // if ($request->boolean('all')) {
        //     $content = \App\Models\ContentItem::with(['category', 'provider'])
        //         ->paginate($perPage);
        // } else {
        // }

        $content = $this->contentService->getContentCatalog($perPage);


        return response()->json($content);
    }

    /**
     * Search content.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:categories,id',
            'type' => 'nullable|in:movie,show,skit,afrimation,real_estate',
            'maturity_rating' => 'nullable|in:all,pg,pg13,r,adult',
            'release_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'genre' => 'nullable|string',
            'visibility' => 'nullable|in:public,private,premium',
            'provider_id' => 'nullable|integer|exists:content_providers,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $dto = ContentSearchDTO::fromRequest($request->all());
        $results = $this->contentService->searchContent($dto);

        return response()->json($results);
    }

    /**
     * Get content by category.
     */
    public function byCategory(int $categoryId, Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $content = $this->contentService->getContentByCategory($categoryId, $perPage);

        return response()->json($content);
    }

    /**
     * Get content details.
     */
    public function show(string $id): JsonResponse
    {
        $content = $this->contentService->getContentDetails($id);

        if (!$content) {
            return response()->json([
                'message' => 'Content not found',
            ], 404);
        }

        return response()->json([
            'data' => $content,
        ]);
    }

    /**
     * Get most viewed content.
     */
    public function mostViewed(): JsonResponse
    {
        $content = $this->contentService->getMostViewed(20);

        return response()->json([
            'data' => $content,
        ]);
    }

    /**
     * Get recently added content.
     */
    public function recentlyAdded(): JsonResponse
    {
        $content = $this->contentService->getRecentlyAdded(20);

        return response()->json([
            'data' => $content,
        ]);
    }

    /**
     * Get new releases (same as recently added).
     * 
     * GET /api/content/new-releases
     */
    public function newReleases(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);
        $content = $this->contentService->getRecentlyAdded($limit);

        return response()->json([
            'data' => $content,
        ]);
    }

    /**
     * Get all categories.
     */
    public function categories(): JsonResponse
    {
        $categories = $this->contentService->getAllCategories();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Get trending content.
     * 
     * GET /api/content/trending
     */
    public function trending(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);
        $content = $this->contentService->getTrending($limit);

        return response()->json([
            'data' => $content,
        ]);
    }

    /**
     * Get content recommendations for authenticated user.
     * 
     * GET /api/content/recommendations
     */
    public function recommendations(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);
        $content = $this->contentService->getRecommendations($request->user(), $limit);

        return response()->json([
            'data' => $content,
        ]);
    }

    /**
     * Get content by genre/category slug.
     * 
     * GET /api/content/genre/{slug}
     */
    public function byGenre(string $slug, Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        $content = $this->contentService->getContentByGenre($slug, $perPage);

        return response()->json($content);
    }

    /**
     * Get similar content.
     * 
     * GET /api/content/{id}/similar
     */
    public function similar(int $id, Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $content = $this->contentService->getSimilarContent($id, $limit);

        return response()->json([
            'data' => $content,
        ]);
    }

    /**
     * Get content by type (movie, series).
     * 
     * GET /api/content/type/{type}?per_page=20
     */
    public function byType(string $type, Request $request): JsonResponse
    {
                    // $table->enum('type', ['movie', 'show', 'skit', 'afrimation', 'real_estate'])->default('movie');

        if (!in_array($type, ['movie', 'series', 'skit', 'afrimation', 'real_estate'])) {
            return response()->json([
                'message' => 'Invalid content type. Must be movie or series.',
            ], 400);
        }

        $perPage = $request->get('per_page', 20);
        $content = $this->contentService->getContentByType($type, $perPage);

        return response()->json($content);
    }

    /**
     * Get featured/hero content for homepage.
     * 
     * GET /api/content/featured
     */
    public function featured(): JsonResponse
    {
        $content = $this->contentService->getFeaturedContent();

        return response()->json([
            'data' => $content,
        ]);
    }
}
