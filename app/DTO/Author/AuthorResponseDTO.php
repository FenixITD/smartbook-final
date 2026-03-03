<?php

declare(strict_types=1);

namespace App\DTO\Author;

use App\Models\Author;
use Illuminate\Database\Eloquent\Collection;

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

    /**
     * @param  Collection<int, Author>  $authors
     * @return array<int, self>
     */
    public static function fromCollection(Collection $authors): array
    {
        return $authors->map(fn ($author) => self::fromModel($author))->all();
    }
}
