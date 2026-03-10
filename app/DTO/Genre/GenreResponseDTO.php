<?php

declare(strict_types=1);

namespace App\DTO\Genre;

use App\Models\Genre;

final readonly class GenreResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $description,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromModel(Genre $genre): self
    {
        return new self(
            id: $genre->id,
            name: $genre->name,
            slug: $genre->slug,
            description: $genre->description,
            createdAt: $genre->created_at->toDateTimeString(),
            updatedAt: $genre->updated_at->toDateTimeString(),
        );
    }
}
