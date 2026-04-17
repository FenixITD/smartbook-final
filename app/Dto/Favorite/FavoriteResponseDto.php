<?php

declare(strict_types=1);

namespace App\Dto\Favorite;

use App\Models\Favorite;

final readonly class FavoriteResponseDto
{
    public static function fromModel(Favorite $favorite): self
    {
        return new self(
            id: $favorite->id,
            userId: $favorite->user_id,
            bookId: $favorite->book_id,
            createdAt: $favorite->created_at?->toDateTimeString() ?? '',
            updatedAt: $favorite->updated_at?->toDateTimeString() ?? '',
        );
    }

    public function __construct(
        public int $id,
        public int $userId,
        public int $bookId,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }
}
