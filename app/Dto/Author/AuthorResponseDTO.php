<?php

declare(strict_types=1);

namespace App\DTO\Author;

use App\Models\Author;

final readonly class AuthorResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromModel(Author $author): self
    {
        return new self(
            id: $author->id,
            name: $author->name,
            createdAt: $author->created_at->toDateTimeString(),
            updatedAt: $author->updated_at->toDateTimeString(),
        );
    }
}
