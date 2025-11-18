<?php

namespace App\DTOs;

readonly class CreateContentDTO
{
    public function __construct(
        public string $title,
        public string $description,
        public string $type,
        public ?int $categoryId = null,
        public ?string $genre = null,
        public int $providerId,
        public ?int $showId = null,
        public ?string $posterUrl = null,
        public ?string $backdropUrl = null,
        public ?string $thumbnailUrl = null,
        public ?string $trailerUrl = null,
        public string $visibility = 'public',
        public ?string $maturityRating = 'all',
        public ?int $releaseYear = null,
        public ?int $durationSeconds = null,
        public array $metadata = [],
        public mixed $publishedAt = null,
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'],
            type: $data['type'],
            categoryId: $data['category_id'] ?? null,
            genre: $data['genre'] ?? null,
            providerId: $data['provider_id'],
            showId: $data['show_id'] ?? null,
            posterUrl: $data['poster_url'] ?? null,
            backdropUrl: $data['backdrop_url'] ?? null,
            thumbnailUrl: $data['thumbnail_url'] ?? null,
            trailerUrl: $data['trailer_url'] ?? null,
            visibility: $data['visibility'] ?? 'public',
            maturityRating: $data['maturity_rating'] ?? 'all',
            releaseYear: $data['release_year'] ?? null,
            durationSeconds: $data['duration_seconds'] ?? null,
            metadata: $data['metadata'] ?? [],
            publishedAt: $data['published_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'category_id' => $this->categoryId,
            'genre' => $this->genre,
            'provider_id' => $this->providerId,
            'show_id' => $this->showId,
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
        ];
    }
}
