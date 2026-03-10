<?php

declare(strict_types=1);

namespace App\Services\Favorite;

use App\DTO\Favorite\FavoriteDTO;
use App\DTO\Favorite\FavoriteResponseDTO;
use App\Models\Favorite;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;

final readonly class UpdateFavoriteService
{
    public function __construct(
        private FavoriteRepositoryInterface $repository
    ) {}

    public function execute(Favorite $favorite, FavoriteDTO $dto): FavoriteResponseDTO
    {
        $this->repository->update($favorite, [
            'userId' => $dto->userId,
            'bookId' => $dto->bookId,
        ]);

        return FavoriteResponseDTO::fromModel($favorite->fresh());
    }
}
