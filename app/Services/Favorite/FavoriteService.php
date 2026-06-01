<?php

declare(strict_types=1);

namespace App\Services\Favorite;

use App\Dto\Favorite\FavoriteFiltersDto;
use App\Dto\PaginatedResponseDto;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;

final readonly class FavoriteService
{
    public function __construct(
        private FavoriteRepositoryInterface $favoriteRepository,
        private BookRepositoryInterface $bookRepository,
    ) {
    }

    public function getBooksByUser(int $userId, FavoriteFiltersDto $filters): PaginatedResponseDto|null
    {
        $favoriteBookIds = $this->favoriteRepository->getBookIdsByUser($userId);

        if ($favoriteBookIds === []) {
            return null;
        }

        return $this->bookRepository->getByIdsWithAuthor($favoriteBookIds, $filters->perPage);
    }
}
