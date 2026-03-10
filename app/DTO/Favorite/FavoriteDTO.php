<?php

declare(strict_types=1);

namespace App\DTO\Favorite;

use App\Http\Requests\Favorite\FavoriteDataRequest;

final readonly class FavoriteDTO
{
    public function __construct(
        public int $userId,
        public int $bookId,
    ) {}

    public static function fromRequest(FavoriteDataRequest $request): self
    {
        return new self(
            userId: $request->integer('userId'),
            bookId: $request->integer('bookId'),
        );
    }
}
