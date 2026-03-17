<?php

declare(strict_types=1);

namespace App\Services\Favorite;

use App\DTO\Favorite\FavoriteDto;
use App\DTO\Favorite\FavoriteResponseDto;
use App\Models\Favorite;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;

final readonly class UpdateFavoriteService
{
    public function __construct(
        private FavoriteRepositoryInterface $repository
    ) {}

    public function execute(Favorite $cartItem, FavoriteDto $dto): FavoriteResponseDto
    {
        $this->repository->update($cartItem, [
            'user_id' => $dto->userId,
            'book_id' => $dto->bookId,
        ]);

        return FavoriteResponseDto::fromModel($cartItem->fresh());
    }
}
