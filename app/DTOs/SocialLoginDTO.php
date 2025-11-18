<?php

namespace App\DTOs;

readonly class SocialLoginDTO
{
    public function __construct(
        public string $provider,
        public string $providerId,
        public string $email,
        public string $name,
        public ?string $avatarUrl = null,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            provider: $data['provider'],
            providerId: $data['provider_id'],
            email: $data['email'],
            name: $data['name'],
            avatarUrl: $data['avatar_url'] ?? null,
        );
    }
}
