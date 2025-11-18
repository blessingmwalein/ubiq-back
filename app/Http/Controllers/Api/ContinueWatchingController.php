<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PlaybackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContinueWatchingController extends Controller
{
    public function __construct(
        private PlaybackService $playbackService
    ) {
    }

    /**
     * Get continue watching list for authenticated user's profile.
     * 
     * GET /api/continue-watching?profile_id=1&limit=20
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $profileId = $request->get('profile_id');
        $limit = $request->get('limit', 20);

        $continueWatching = $this->playbackService->getContinueWatching($profileId, $limit);

        return response()->json([
            'data' => $continueWatching,
        ]);
    }

    /**
     * Get recently watched content for profile.
     * 
     * GET /api/recently-watched?profile_id=1&limit=20
     */
    public function recentlyWatched(Request $request): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $profileId = $request->get('profile_id');
        $limit = $request->get('limit', 20);

        $recentlyWatched = $this->playbackService->getRecentlyWatched($profileId, $limit);

        return response()->json([
            'data' => $recentlyWatched,
        ]);
    }

    /**
     * Mark content as completed.
     * 
     * POST /api/continue-watching/complete
     * Body: { profile_id, content_item_id }
     */
    public function markCompleted(Request $request): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
            'content_item_id' => 'required|exists:content_items,id',
        ]);

        $this->playbackService->markAsCompleted(
            $request->profile_id,
            $request->content_item_id
        );

        return response()->json([
            'message' => 'Content marked as completed',
        ]);
    }

    /**
     * Remove content from continue watching.
     * 
     * DELETE /api/continue-watching
     * Body: { profile_id, content_item_id }
     */
    public function remove(Request $request): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
            'content_item_id' => 'required|exists:content_items,id',
        ]);

        // Mark as completed to remove from continue watching
        $this->playbackService->markAsCompleted(
            $request->profile_id,
            $request->content_item_id
        );

        return response()->json([
            'message' => 'Content removed from continue watching',
        ]);
    }
}
