<?php

declare(strict_types=1);

namespace App\Dto\Author;

use App\Models\Author;

class AuthorResponseDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $createdAt,
        public string $updatedAt,
        public int $booksCount = 0,
    ) {
    }

    public static function fromModel(Author $author): self
    {
        return new self(
            id: $author->id,
            name: $author->name,
            createdAt: $author->created_at?->toDateTimeString() ?? '',
            updatedAt: $author->updated_at?->toDateTimeString() ?? '',
            booksCount: $author->books_count ?? 0,
        );
    }
}
