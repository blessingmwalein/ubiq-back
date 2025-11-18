<?php

namespace App\Contracts\Repositories;

use App\Models\VideoAsset;
use Illuminate\Database\Eloquent\Collection;

interface VideoAssetRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get video assets by content item.
     */
    public function getByContentItem(int $contentItemId): Collection;

    /**
     * Get video asset by quality and type.
     */
    public function getByQualityAndType(int $contentItemId, string $quality, string $type): ?VideoAsset;

    /**
     * Get master video asset.
     */
    public function getMasterAsset(int $contentItemId): ?VideoAsset;

    /**
     * Get HLS assets.
     */
    public function getHlsAssets(int $contentItemId): Collection;

    /**
     * Update processing status.
     */
    public function updateProcessingStatus(int $videoAssetId, string $status): bool;

    /**
     * Get assets ready for transcoding.
     */
    public function getReadyForTranscoding(): Collection;

    /**
     * Get total storage size for content.
     */
    public function getTotalStorageSize(int $contentItemId): int;
}
