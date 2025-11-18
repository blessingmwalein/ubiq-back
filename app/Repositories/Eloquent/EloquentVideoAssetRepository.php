<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\VideoAssetRepositoryInterface;
use App\Models\VideoAsset;
use Illuminate\Database\Eloquent\Collection;

class EloquentVideoAssetRepository extends EloquentRepository implements VideoAssetRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(VideoAsset $model)
    {
        $this->model = $model;
    }

    /**
     * Get video assets by content item.
     */
    public function getByContentItem(int $contentItemId): Collection
    {
        return $this->model->where('content_item_id', $contentItemId)->get();
    }

    /**
     * Get video asset by quality and type.
     */
    public function getByQualityAndType(int $contentItemId, string $quality, string $type): ?VideoAsset
    {
        return $this->model
            ->where('content_item_id', $contentItemId)
            ->where('quality', $quality)
            ->where('type', $type)
            ->first();
    }

    /**
     * Get master video asset.
     */
    public function getMasterAsset(int $contentItemId): ?VideoAsset
    {
        return $this->model
            ->where('content_item_id', $contentItemId)
            ->where('type', 'master')
            ->first();
    }

    /**
     * Get HLS assets.
     */
    public function getHlsAssets(int $contentItemId): Collection
    {
        return $this->model
            ->where('content_item_id', $contentItemId)
            ->where('type', 'hls')
            ->orderBy('quality')
            ->get();
    }

    /**
     * Update processing status.
     */
    public function updateProcessingStatus(int $videoAssetId, string $status): bool
    {
        return $this->update($videoAssetId, ['processing_status' => $status]);
    }

    /**
     * Get assets ready for transcoding.
     */
    public function getReadyForTranscoding(): Collection
    {
        return $this->model
            ->where('type', 'master')
            ->where('processing_status', 'pending')
            ->get();
    }

    /**
     * Get total storage size for content.
     */
    public function getTotalStorageSize(int $contentItemId): int
    {
        return (int) $this->model
            ->where('content_item_id', $contentItemId)
            ->sum('file_size');
    }
}
