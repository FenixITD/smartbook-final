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

    public static function fromModel(Favorite $cartItem): self
    {
        return new self(
            id: $cartItem->id,
            userId: (int) $cartItem->userId,
            bookId: (int) $cartItem->bookId,
            createdAt: $cartItem->created_at->toDateTimeString(),
            updatedAt: $cartItem->updated_at->toDateTimeString(),
        );
    }
}
