<?php

namespace App\DTOs;

readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $role = 'customer',
        public ?string $socialProvider = null,
        public ?string $socialProviderId = null,
        public ?string $avatarUrl = null,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'] ?? '',
            role: $data['role'] ?? 'customer',
            socialProvider: $data['social_provider'] ?? null,
            socialProviderId: $data['social_provider_id'] ?? null,
            avatarUrl: $data['avatar_url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'social_provider' => $this->socialProvider,
            'social_provider_id' => $this->socialProviderId,
            'avatar_url' => $this->avatarUrl,
        ];
    }
}
