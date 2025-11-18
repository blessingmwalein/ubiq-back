<?php

namespace App\DTOs;

readonly class UpdateContentDTO
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?int $categoryId = null,
        public ?string $genre = null,
        public ?string $posterUrl = null,
        public ?string $backdropUrl = null,
        public ?string $thumbnailUrl = null,
        public ?string $trailerUrl = null,
        public ?string $visibility = null,
        public ?string $maturityRating = null,
        public ?int $releaseYear = null,
        public ?int $durationSeconds = null,
        public ?array $metadata = null,
        public mixed $publishedAt = null,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            categoryId: $data['category_id'] ?? null,
            genre: $data['genre'] ?? null,
            posterUrl: $data['poster_url'] ?? null,
            backdropUrl: $data['backdrop_url'] ?? null,
            thumbnailUrl: $data['thumbnail_url'] ?? null,
            trailerUrl: $data['trailer_url'] ?? null,
            visibility: $data['visibility'] ?? null,
            maturityRating: $data['maturity_rating'] ?? null,
            releaseYear: $data['release_year'] ?? null,
            durationSeconds: $data['duration_seconds'] ?? null,
            metadata: $data['metadata'] ?? null,
            publishedAt: $data['published_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'genre' => $this->genre,
            'poster_url' => $this->posterUrl,
            'backdrop_url' => $this->backdropUrl,
            'thumbnail_url' => $this->thumbnailUrl,
            'trailer_url' => $this->trailerUrl,
            'visibility' => $this->visibility,
            'maturity_rating' => $this->maturityRating,
            'release_year' => $this->releaseYear,
            'duration_seconds' => $this->durationSeconds,
            'metadata' => $this->metadata,
            'published_at' => $this->publishedAt,
        ], fn($value) => $value !== null);
    }
}
