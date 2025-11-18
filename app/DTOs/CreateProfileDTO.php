<?php

namespace App\DTOs;

readonly class CreateProfileDTO
{
    public function __construct(
        public int $accountId,
        public string $name,
        public ?string $avatarUrl = null,
        public string $maturityRating = 'G',
        public bool $isPrimary = false,
        public array $interestIds = [],
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            accountId: $data['account_id'],
            name: $data['name'],
            avatarUrl: $data['avatar_url'] ?? null,
            maturityRating: $data['maturity_rating'] ?? 'G',
            isPrimary: $data['is_primary'] ?? false,
            interestIds: $data['interest_ids'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'account_id' => $this->accountId,
            'name' => $this->name,
            'avatar_url' => $this->avatarUrl,
            'maturity_rating' => $this->maturityRating,
            'is_primary' => $this->isPrimary,
        ];
    }
}
