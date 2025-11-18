<?php

namespace App\DTOs;

readonly class CreateSubscriptionDTO
{
    public function __construct(
        public int $accountId,
        public int $packageId,
        public ?string $paymentMethod = 'stripe',
        public ?string $paymentToken = null,
        public bool $isTrial = false,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            accountId: $data['account_id'],
            packageId: $data['package_id'],
            paymentMethod: $data['payment_method'] ?? 'stripe',
            paymentToken: $data['payment_token'] ?? null,
            isTrial: $data['is_trial'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'account_id' => $this->accountId,
            'package_id' => $this->packageId,
            'payment_method' => $this->paymentMethod,
            'payment_token' => $this->paymentToken,
            'is_trial' => $this->isTrial,
        ];
    }
}
