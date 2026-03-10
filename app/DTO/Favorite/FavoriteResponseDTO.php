<?php

declare(strict_types=1);

namespace App\DTO\Favorite;

use App\Models\Favorite;

final readonly class FavoriteResponseDTO
{
    public function __construct(
        public int $id,
        public int $userId,
        public int $bookId,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromModel(Favorite $favorite): self
    {
        return new self(
            id: $favorite->id,
            userId: (int) $favorite->userId,
            bookId: (int) $favorite->bookId,
            createdAt: $favorite->created_at->toDateTimeString(),
            updatedAt: $favorite->updated_at->toDateTimeString(),
        );
    }
}
