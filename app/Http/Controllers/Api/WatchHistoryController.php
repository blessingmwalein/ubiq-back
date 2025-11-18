<?php

namespace App\Http\Controllers\Api;

use App\DTOs\UpdateWatchProgressDTO;
use App\Http\Controllers\Controller;
use App\Services\PlaybackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WatchHistoryController extends Controller
{
    public function __construct(
        private PlaybackService $playbackService
    ) {
    }

    /**
     * Get continue watching list.
     */
    public function continueWatching(Request $request): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
        ]);

        $content = $this->playbackService->getContinueWatching($request->profile_id);

        return response()->json([
            'data' => $content,
        ]);
    }

    /**
     * Get recently watched.
     */
    public function recentlyWatched(Request $request): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
        ]);

        $content = $this->playbackService->getRecentlyWatched($request->profile_id);

        return response()->json([
            'data' => $content,
        ]);
    }

    /**
     * Update watch progress.
     */
    public function updateProgress(Request $request): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
            'content_item_id' => 'required|exists:content_items,id',
            'progress_seconds' => 'required|integer|min:0',
            'duration_seconds' => 'required|integer|min:1',
        ]);

        $dto = UpdateWatchProgressDTO::fromRequest($request->all());
        $watchHistory = $this->playbackService->trackWatchProgress($dto);

        return response()->json([
            'message' => 'Watch progress updated',
            'data' => $watchHistory,
        ]);
    }
}
