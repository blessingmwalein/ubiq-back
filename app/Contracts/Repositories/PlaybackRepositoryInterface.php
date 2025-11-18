<?php

namespace App\Contracts\Repositories;

use App\Models\PlaybackToken;
use Illuminate\Database\Eloquent\Collection;

interface PlaybackRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get playback tokens by profile.
     */
    public function getByProfile(int $profileId): Collection;

    /**
     * Get playback tokens by content item.
     */
    public function getByContentItem(int $contentItemId): Collection;

    /**
     * Find valid token.
     */
    public function findValidToken(string $token): ?PlaybackToken;

    /**
     * Create playback token.
     */
    public function createToken(int $profileId, int $contentItemId, string $ipAddress, array $metadata = []): PlaybackToken;

    /**
     * Revoke token.
     */
    public function revokeToken(string $token): bool;

    /**
     * Clean expired tokens.
     */
    public function cleanExpiredTokens(): int;

    /**
     * Get active tokens for profile.
     */
    public function getActiveTokensForProfile(int $profileId): Collection;

    /**
     * Get token usage statistics.
     */
    public function getTokenUsageStats(int $profileId): array;
}
