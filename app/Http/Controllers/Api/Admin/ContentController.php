<?php

namespace App\Http\Controllers\Api\Admin;

use App\DTOs\CreateContentDTO;
use App\DTOs\UpdateContentDTO;
use App\Http\Controllers\Controller;
use App\Services\ContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(
        private ContentService $contentService
    ) {
    }

    /**
     * Create new content.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:movie,series,episode',
            'category_id' => 'required|exists:categories,id',
            'content_provider_id' => 'required|exists:content_providers,id',
            'show_id' => 'nullable|exists:shows,id',
            'poster_url' => 'nullable|url',
            'thumbnail_url' => 'nullable|url',
            'trailer_url' => 'nullable|url',
            'visibility' => 'nullable|in:public,private,premium',
            'maturity_rating' => 'nullable|in:G,PG,PG-13,R,NC-17',
            'release_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'duration' => 'nullable|integer',
            'metadata' => 'nullable|array',
            'published_at' => 'nullable|date',
        ]);

        // Auto-set visibility to public if not provided
        $data = $request->all();
        if (!isset($data['visibility'])) {
            $data['visibility'] = 'public';
        }
        
        // Auto-set published_at to now if not provided and visibility is public
        if (!isset($data['published_at']) && $data['visibility'] === 'public') {
            $data['published_at'] = now();
        }

        $dto = CreateContentDTO::fromRequest($data);
        $content = $this->contentService->createContent($dto);

        return response()->json([
            'message' => 'Content created successfully',
            'data' => $content,
        ], 201);
    }

    /**
     * Update content.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
            'poster_url' => 'nullable|url',
            'thumbnail_url' => 'nullable|url',
            'trailer_url' => 'nullable|url',
            'visibility' => 'nullable|in:public,private,premium',
            'maturity_rating' => 'nullable|in:G,PG,PG-13,R,NC-17',
            'release_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'duration' => 'nullable|integer',
            'metadata' => 'nullable|array',
            'published_at' => 'nullable|date',
        ]);

        $data = $request->all();
        
        // If visibility is changed to public and published_at is not set, auto-set it
        if (isset($data['visibility']) && $data['visibility'] === 'public' && !isset($data['published_at'])) {
            $content = $this->contentService->getContentDetails($id);
            if ($content && !$content->published_at) {
                $data['published_at'] = now();
            }
        }

        $dto = UpdateContentDTO::fromRequest($data);
        $updated = $this->contentService->updateContent($id, $dto);

        if (!$updated) {
            return response()->json([
                'message' => 'Content not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Content updated successfully',
        ]);
    }

    /**
     * Delete content.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->contentService->deleteContent($id);

        if (!$deleted) {
            return response()->json([
                'message' => 'Content not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Content deleted successfully',
        ]);
    }

    /**
     * Publish content.
     */
    public function publish(int $id): JsonResponse
    {
        $published = $this->contentService->publishContent($id);

        if (!$published) {
            return response()->json([
                'message' => 'Content not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Content published successfully',
        ]);
    }

    /**
     * Unpublish content.
     */
    public function unpublish(int $id): JsonResponse
    {
        $unpublished = $this->contentService->unpublishContent($id);

        if (!$unpublished) {
            return response()->json([
                'message' => 'Content not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Content unpublished successfully',
        ]);
    }
}
