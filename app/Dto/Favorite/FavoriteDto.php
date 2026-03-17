<?php

declare(strict_types=1);

namespace App\Dto\Favorite;

use App\Http\Requests\Favorite\FavoriteDataRequest;

final readonly class FavoriteDto
{
    public function __construct(
        public int $userId,
        public int $bookId,
    ) {}
}
