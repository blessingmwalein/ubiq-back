<?php

namespace App\DTOs;

readonly class UpdateWatchProgressDTO
{
    public function __construct(
        public int $profileId,
        public int $contentItemId,
        public int $progressSeconds,
        public int $durationSeconds,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            profileId: $data['profile_id'],
            contentItemId: $data['content_item_id'],
            progressSeconds: $data['progress_seconds'],
            durationSeconds: $data['duration_seconds'],
        );
    }
}
