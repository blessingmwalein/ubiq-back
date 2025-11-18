<?php

namespace App\DTOs;

readonly class PlaybackRequestDTO
{
    public function __construct(
        public int $profileId,
        public int $contentItemId,
        public string $ipAddress,
        public ?string $userAgent = null,
        public array $metadata = [],
    ) {
    }

    public static function fromRequest(array $data, string $ipAddress, ?string $userAgent = null): self
    {
        return new self(
            profileId: $data['profile_id'],
            contentItemId: $data['content_item_id'],
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: $data['metadata'] ?? [],
        );
    }
}
