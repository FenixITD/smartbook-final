<?php

declare(strict_types=1);

namespace App\Services\Favorite;

use App\DTO\Favorite\FavoriteDto;
use App\DTO\Favorite\FavoriteResponseDto;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;

final readonly class CreateFavoriteService
{
    public function __construct(
        private FavoriteRepositoryInterface $repository
    ) {}

    public function execute(FavoriteDto $dto): FavoriteResponseDto
    {
        return $this->repository->create([
            'user_id' => $dto->userId,
            'book_id' => $dto->bookId,
        ]);
    }
}
