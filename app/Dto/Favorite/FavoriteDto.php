<?php

declare(strict_types=1);

namespace App\Dto\Favorite;

final readonly class FavoriteDto
{
    public function __construct(
        public int $userId,
        public int $bookId,
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'book_id' => $this->bookId,
        ];
    }
}
