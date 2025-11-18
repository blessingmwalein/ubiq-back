<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\PlaybackRepositoryInterface;
use App\Models\PlaybackToken;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class EloquentPlaybackRepository extends EloquentRepository implements PlaybackRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(PlaybackToken $model)
    {
        $this->model = $model;
    }

    /**
     * Get playback tokens by profile.
     */
    public function getByProfile(int $profileId): Collection
    {
        return $this->model->where('profile_id', $profileId)->latest()->get();
    }

    /**
     * Get playback tokens by content item.
     */
    public function getByContentItem(int $contentItemId): Collection
    {
        return $this->model->where('content_item_id', $contentItemId)->latest()->get();
    }

    /**
     * Find valid token.
     */
    public function findValidToken(string $token): ?PlaybackToken
    {
        return $this->model
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();
    }

    /**
     * Create playback token.
     */
    public function createToken(int $profileId, int $contentItemId, string $ipAddress, array $metadata = []): PlaybackToken
    {
        return $this->create([
            'profile_id' => $profileId,
            'content_item_id' => $contentItemId,
            'token' => Str::random(64),
            'client_ip' => $ipAddress,
            'expires_at' => now()->addHours(6),
        ]);
    }

    /**
     * Revoke token (mark as used).
     */
    public function revokeToken(string $token): bool
    {
        $playbackToken = $this->model->where('token', $token)->first();
        
        if (!$playbackToken) {
            return false;
        }

        return $playbackToken->update(['used_at' => now()]);
    }

    /**
     * Clean expired tokens.
     */
    public function cleanExpiredTokens(): int
    {
        return $this->model
            ->where('expires_at', '<', now())
            ->delete();
    }

    /**
     * Get active tokens for profile.
     */
    public function getActiveTokensForProfile(int $profileId): Collection
    {
        return $this->model
            ->where('profile_id', $profileId)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->get();
    }

    /**
     * Get token usage statistics.
     */
    public function getTokenUsageStats(int $profileId): array
    {
        $total = $this->model->where('profile_id', $profileId)->count();
        $active = $this->model
            ->where('profile_id', $profileId)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->count();
        $used = $this->model
            ->where('profile_id', $profileId)
            ->whereNotNull('used_at')
            ->count();
        $expired = $this->model
            ->where('profile_id', $profileId)
            ->where('expires_at', '<=', now())
            ->whereNull('used_at')
            ->count();

        return [
            'total' => $total,
            'active' => $active,
            'used' => $used,
            'expired' => $expired,
        ];
    }
}
