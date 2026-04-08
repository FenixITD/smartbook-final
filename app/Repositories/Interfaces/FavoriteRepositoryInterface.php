<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Favorite\FavoriteDto;
use App\DTO\Favorite\FavoriteFiltersDto;
use App\DTO\Favorite\FavoriteResponseDto;
use App\Models\Favorite;

interface FavoriteRepositoryInterface
{
    /**
     * @return array<FavoriteResponseDto>
     */
    public function getList(FavoriteFiltersDto $filters): array;

    public function getById(int $id): ?FavoriteResponseDto;

    public function create(FavoriteDto $data): FavoriteResponseDto;

    public function update(int $id, FavoriteDto $data): ?FavoriteResponseDto;

    public function delete(int $id): bool;
}
