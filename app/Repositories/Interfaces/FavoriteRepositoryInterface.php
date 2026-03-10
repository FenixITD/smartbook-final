<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\DTO\Favorite\FavoriteFiltersDTO;
use App\DTO\Favorite\FavoriteResponseDTO;
use App\Models\Favorite;

interface FavoriteRepositoryInterface
{
    /**
     * @return array<FavoriteResponseDTO>
     */
    public function getList(FavoriteFiltersDTO $filters): array;

    public function getById(int $id): ?FavoriteResponseDTO;

    public function create(array $data): FavoriteResponseDTO;

    public function update(Favorite $favorite, array $data): ?FavoriteResponseDTO;

    public function delete(Favorite $favorite): bool;
}
