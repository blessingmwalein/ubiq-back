<?php

namespace App\Services;

use App\Contracts\Repositories\PlaybackRepositoryInterface;
use App\Contracts\Repositories\WatchHistoryRepositoryInterface;
use App\Contracts\Repositories\ContentRepositoryInterface;
use App\DTOs\PlaybackRequestDTO;
use App\DTOs\UpdateWatchProgressDTO;
use App\Models\PlaybackToken;
use App\Models\WatchHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlaybackService
{
    public function __construct(
        private PlaybackRepositoryInterface $playbackRepository,
        private WatchHistoryRepositoryInterface $watchHistoryRepository,
        private ContentRepositoryInterface $contentRepository,
    ) {
    }

    /**
     * Generate playback token for content.
     */
    public function generatePlaybackToken(PlaybackRequestDTO $dto): PlaybackToken
    {
        // Verify content exists and is published
        $content = $this->contentRepository->find($dto->contentItemId);
        
        if (!$content || !$content->published_at) {
            throw new \Exception('Content not available for playback.');
        }

        // Create playback token
        return $this->playbackRepository->createToken(
            $dto->profileId,
            $dto->contentItemId,
            $dto->ipAddress,
            array_merge($dto->metadata, [
                'user_agent' => $dto->userAgent,
                'created_at' => now()->toIso8601String(),
            ])
        );
    }

    /**
     * Validate playback token.
     */
    public function validateToken(string $token): ?PlaybackToken
    {
        return $this->playbackRepository->findValidToken($token);
    }

    /**
     * Get streaming URL for content.
     */
    public function getStreamingUrl(string $token): ?array
    {
        $playbackToken = $this->validateToken($token);

        if (!$playbackToken) {
            return null;
        }

        $content = $this->contentRepository->getWithRelations($playbackToken->content_item_id);

        if (!$content) {
            return null;
        }

        // Get HLS manifest URL (prefer 1080p)
        $hlsAsset = $content->videoAssets()
            ->where('rendition_key', '1080p')
            ->first();

        if (!$hlsAsset) {
            // Fallback to any available asset
            $hlsAsset = $content->videoAssets()->first();
        }

        if (!$hlsAsset) {
            return null;
        }

        // Get storage disk (using public disk for local videos)
        $disk = Storage::disk('public');
        
        // Remove 'public/' prefix if it exists (for backward compatibility with old data)
        $path = $hlsAsset->hls_manifest_key;
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7); // Remove 'public/' prefix
        }
        
        // Generate public URL for HLS manifest
        $manifestUrl = $disk->url($path);

        return [
            'manifest_url' => $manifestUrl,
            'type' => 'application/x-mpegURL',
            'token' => $token,
            'expires_at' => $playbackToken->expires_at,
        ];
    }

    /**
     * Track watch progress.
     */
    public function trackWatchProgress(UpdateWatchProgressDTO $dto): WatchHistory
    {
        return $this->watchHistoryRepository->updateProgress(
            $dto->profileId,
            $dto->contentItemId,
            $dto->progressSeconds,
            $dto->durationSeconds
        );
    }

    /**
     * Get continue watching list for profile.
     */
    public function getContinueWatching(int $profileId, int $limit = 10): Collection
    {
        return $this->watchHistoryRepository->getContinueWatching($profileId, $limit);
    }

    /**
     * Get recently watched for profile.
     */
    public function getRecentlyWatched(int $profileId, int $limit = 20): Collection
    {
        return $this->watchHistoryRepository->getRecentlyWatched($profileId, $limit);
    }

    /**
     * Mark content as completed.
     */
    public function markAsCompleted(int $profileId, int $contentItemId): bool
    {
        return $this->watchHistoryRepository->markAsCompleted($profileId, $contentItemId);
    }

    /**
     * Revoke playback token.
     */
    public function revokeToken(string $token): bool
    {
        return $this->playbackRepository->revokeToken($token);
    }

    /**
     * Clean expired tokens.
     */
    public function cleanExpiredTokens(): int
    {
        return $this->playbackRepository->cleanExpiredTokens();
    }

    /**
     * Get active tokens for profile.
     */
    public function getActiveTokens(int $profileId): Collection
    {
        return $this->playbackRepository->getActiveTokensForProfile($profileId);
    }

    /**
     * Get token usage statistics.
     */
    public function getTokenUsageStats(int $profileId): array
    {
        return $this->playbackRepository->getTokenUsageStats($profileId);
    }

    /**
     * Get HLS manifest with all quality variants.
     */
    public function getHlsManifest(string $token): ?array
    {
        $playbackToken = $this->validateToken($token);

        if (!$playbackToken) {
            return null;
        }

        $content = $this->contentRepository->getWithRelations($playbackToken->content_item_id);

        if (!$content) {
            return null;
        }

        // Get all HLS assets
        $hlsAssets = $content->videoAssets()
            ->orderByRaw("FIELD(rendition_key, '1080p', '720p', '480p', '360p')")
            ->get();

        if ($hlsAssets->isEmpty()) {
            return null;
        }

        $disk = Storage::disk('public');
        $variants = [];

        foreach ($hlsAssets as $asset) {
            // Generate public URL for local video
            $url = $disk->url($asset->hls_manifest_key);

            $variants[] = [
                'quality' => $asset->rendition_key,
                'bandwidth' => $this->getQualityBandwidth($asset->rendition_key),
                'resolution' => $asset->resolution ?? $this->getQualityResolution($asset->rendition_key),
                'url' => $url,
            ];
        }

        // Get resume position
        $watchHistory = $this->watchHistoryRepository->getForProfileAndContent(
            $playbackToken->profile_id,
            $playbackToken->content_item_id
        );

        return [
            'content_id' => $content->id,
            'title' => $content->title,
            'variants' => $variants,
            'resume_position' => $watchHistory?->progress_seconds ?? 0,
            'duration' => $content->duration_minutes * 60,
            'token' => $token,
            'expires_at' => $playbackToken->expires_at,
        ];
    }

    /**
     * Get quality options for content.
     */
    public function getQualityOptions(int $contentId): array
    {
        $content = $this->contentRepository->find($contentId);

        if (!$content) {
            return [];
        }

        $qualities = $content->videoAssets()
            ->select('rendition_key', 'file_size_mb', 'resolution')
            ->distinct()
            ->orderByRaw("FIELD(rendition_key, '1080p', '720p', '480p', '360p')")
            ->get()
            ->map(function ($asset) {
                return [
                    'quality' => $asset->rendition_key,
                    'bandwidth' => $this->getQualityBandwidth($asset->rendition_key),
                    'resolution' => $asset->resolution ?? $this->getQualityResolution($asset->rendition_key),
                    'file_size' => $asset->file_size_mb,
                ];
            });

        return $qualities->toArray();
    }

    /**
     * Get subtitle and audio tracks for content.
     */
    public function getTracks(int $contentId): array
    {
        $content = $this->contentRepository->find($contentId);

        if (!$content) {
            return ['subtitles' => [], 'audio' => []];
        }

        // TODO: Implement subtitle and audio tracks
        // This requires a separate table for subtitles/audio tracks
        // or additional columns in video_assets table
        
        return [
            'subtitles' => [],
            'audio' => [],
        ];
    }

    /**
     * Update watch progress during playback.
     */
    public function updateWatchProgress(string $token, int $position, int $duration): void
    {
        $playbackToken = $this->validateToken($token);

        if (!$playbackToken) {
            throw new \Exception('Invalid playback token');
        }

        $this->watchHistoryRepository->updateProgress(
            $playbackToken->profile_id,
            $playbackToken->content_item_id,
            $position,
            $duration
        );
    }

    /**
     * Get resume position for content and profile.
     */
    public function getResumePosition(int $contentId, int $profileId): int
    {
        $watchHistory = $this->watchHistoryRepository->getForProfileAndContent(
            $profileId,
            $contentId
        );

        return $watchHistory?->progress_seconds ?? 0;
    }

    /**
     * Log playback error for diagnostics.
     */
    public function logPlaybackError(string $token, string $errorCode, string $errorMessage): void
    {
        $playbackToken = $this->validateToken($token);

        if (!$playbackToken) {
            return;
        }

        // Log to Laravel log
        Log::error('Playback error', [
            'token' => $token,
            'profile_id' => $playbackToken->profile_id,
            'content_id' => $playbackToken->content_item_id,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'timestamp' => now()->toIso8601String(),
        ]);

        // Update playback token metadata
        $metadata = $playbackToken->metadata ?? [];
        $metadata['errors'] = $metadata['errors'] ?? [];
        $metadata['errors'][] = [
            'code' => $errorCode,
            'message' => $errorMessage,
            'timestamp' => now()->toIso8601String(),
        ];

        $playbackToken->update(['metadata' => $metadata]);
    }

    /**
     * Get bandwidth for quality level.
     */
    private function getQualityBandwidth(string $quality): int
    {
        return match ($quality) {
            '1080p' => 5000000, // 5 Mbps
            '720p' => 3000000,  // 3 Mbps
            '480p' => 1500000,  // 1.5 Mbps
            '360p' => 800000,   // 800 Kbps
            default => 1000000,
        };
    }

    /**
     * Get resolution for quality level.
     */
    private function getQualityResolution(string $quality): string
    {
        return match ($quality) {
            '1080p' => '1920x1080',
            '720p' => '1280x720',
            '480p' => '854x480',
            '360p' => '640x360',
            default => '1280x720',
        };
    }
}
