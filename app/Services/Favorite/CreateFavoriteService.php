<?php

declare(strict_types=1);

namespace App\Services\Favorite;

use App\DTO\Favorite\FavoriteDTO;
use App\DTO\Favorite\FavoriteResponseDTO;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;

final readonly class CreateFavoriteService
{
    public function __construct(
        private FavoriteRepositoryInterface $repository
    ) {}

    public function execute(FavoriteDTO $dto): FavoriteResponseDTO
    {
        return $this->repository->create([
            'userId' => $dto->userId,
            'bookId' => $dto->bookId,
        ]);
    }
}
