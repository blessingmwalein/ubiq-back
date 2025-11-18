<?php

namespace App\DTOs;

readonly class ContentSearchDTO
{
    public function __construct(
        public string $query,
        public ?int $categoryId = null,
        public ?string $type = null,
        public ?string $maturityRating = null,
        public ?int $releaseYear = null,
        public ?string $genre = null,
        public ?string $visibility = null,
        public ?int $providerId = null,
        public int $perPage = 15,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            query: $data['query'] ?? '',
            categoryId: $data['category_id'] ?? null,
            type: $data['type'] ?? null,
            maturityRating: $data['maturity_rating'] ?? null,
            releaseYear: $data['release_year'] ?? null,
            genre: $data['genre'] ?? null,
            visibility: $data['visibility'] ?? null,
            providerId: $data['provider_id'] ?? null,
            perPage: $data['per_page'] ?? 15,
        );
    }

    public function toFiltersArray(): array
    {
        return array_filter([
            'category_id' => $this->categoryId,
            'type' => $this->type,
            'maturity_rating' => $this->maturityRating,
            'release_year' => $this->releaseYear,
            'genre' => $this->genre,
            'visibility' => $this->visibility,
            'provider_id' => $this->providerId,
            'per_page' => $this->perPage,
        ], fn($value) => $value !== null);
    }
}
