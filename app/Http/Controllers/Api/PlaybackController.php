<?php

namespace App\Http\Controllers\Api;

use App\DTOs\PlaybackRequestDTO;
use App\Http\Controllers\Controller;
use App\Services\PlaybackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaybackController extends Controller
{
    public function __construct(
        private PlaybackService $playbackService
    ) {
    }

    /**
     * Request playback token for content.
     */
    public function requestToken(Request $request): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
            'content_item_id' => 'required|exists:content_items,id',
        ]);

        try {
            $dto = PlaybackRequestDTO::fromRequest(
                $request->all(),
                $request->ip(),
                $request->userAgent()
            );

            $token = $this->playbackService->generatePlaybackToken($dto);

            return response()->json([
                'data' => [
                    'token' => $token->token,
                    'expires_at' => $token->expires_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get streaming URL using token.
     */
    public function getStreamUrl(string $token): JsonResponse
    {
        $streamingUrl = $this->playbackService->getStreamingUrl($token);

        if (!$streamingUrl) {
            return response()->json([
                'message' => 'Invalid or expired token',
            ], 401);
        }

        return response()->json([
            'data' => $streamingUrl,
        ]);
    }

    /**
     * Validate playback token.
     */
    public function validateToken(string $token): JsonResponse
    {
        $playbackToken = $this->playbackService->validateToken($token);

        if (!$playbackToken) {
            return response()->json([
                'message' => 'Invalid or expired token',
            ], 401);
        }

        return response()->json([
            'data' => [
                'valid' => true,
                'expires_at' => $playbackToken->expires_at,
            ],
        ]);
    }

    /**
     * Get HLS streaming manifest with quality options.
     * 
     * GET /api/playback/hls/{token}
     */
    public function getHlsManifest(string $token): JsonResponse
    {
        $manifest = $this->playbackService->getHlsManifest($token);

        if (!$manifest) {
            return response()->json([
                'message' => 'Invalid or expired token',
            ], 401);
        }

        return response()->json([
            'data' => $manifest,
        ]);
    }

    /**
     * Get available quality options for content.
     * 
     * GET /api/playback/qualities/{contentId}
     */
    public function getQualityOptions(int $contentId): JsonResponse
    {
        $qualities = $this->playbackService->getQualityOptions($contentId);

        return response()->json([
            'data' => $qualities,
        ]);
    }

    /**
     * Get subtitle/audio tracks for content.
     * 
     * GET /api/playback/tracks/{contentId}
     */
    public function getTracks(int $contentId): JsonResponse
    {
        $tracks = $this->playbackService->getTracks($contentId);

        return response()->json([
            'data' => $tracks,
        ]);
    }

    /**
     * Update watch progress.
     * 
     * POST /api/playback/progress
     * Body: { token, position, duration }
     */
    public function updateProgress(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'position' => 'required|integer|min:0',
            'duration' => 'required|integer|min:1',
        ]);

        $this->playbackService->updateWatchProgress(
            $request->token,
            $request->position,
            $request->duration
        );

        return response()->json([
            'message' => 'Progress updated successfully',
        ]);
    }

    /**
     * Get resume position for content.
     * 
     * GET /api/playback/resume/{contentId}/{profileId}
     */
    public function getResumePosition(int $contentId, int $profileId): JsonResponse
    {
        $position = $this->playbackService->getResumePosition($contentId, $profileId);

        return response()->json([
            'data' => [
                'position' => $position,
            ],
        ]);
    }

    /**
     * Report playback error.
     * 
     * POST /api/playback/error
     * Body: { token, error_code, error_message }
     */
    public function reportError(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'error_code' => 'required|string',
            'error_message' => 'required|string',
        ]);

        $this->playbackService->logPlaybackError(
            $request->token,
            $request->error_code,
            $request->error_message
        );

        return response()->json([
            'message' => 'Error reported successfully',
        ]);
    }
}
