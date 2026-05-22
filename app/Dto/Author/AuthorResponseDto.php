<?php

declare(strict_types=1);

namespace App\Dto\Author;

use App\Models\Author;

final readonly class AuthorResponseDto
{
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

    public function __construct(
        public int $id,
        public string $name,
        public string $createdAt,
        public string $updatedAt,
        public int $booksCount = 0,
    ) {
    }
}
