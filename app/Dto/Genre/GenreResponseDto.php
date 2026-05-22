<?php

declare(strict_types=1);

namespace App\Dto\Genre;

use App\Models\Genre;

final readonly class GenreResponseDto
{
    public static function fromModel(Genre $genre): self
    {
        return new self(
            id: $genre->id,
            name: $genre->name,
            slug: $genre->slug,
            createdAt: $genre->created_at?->toDateTimeString() ?? '',
            updatedAt: $genre->updated_at?->toDateTimeString() ?? '',
            booksCount: $author->books_count ?? 0,
        );
    }

    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $createdAt,
        public string $updatedAt,
        public int $booksCount = 0,
    ) {
    }
}
