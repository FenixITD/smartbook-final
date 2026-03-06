<?php

declare(strict_types=1);

namespace App\DTO\Author;

use App\Models\Author;

final readonly class AuthorResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromModel(Author $author): self
    {
        return new self(
            id: $author->id,
            name: $author->name,
            created_at: $author->created_at->toDateTimeString(),
            updated_at: $author->updated_at->toDateTimeString(),
        );
    }
}
