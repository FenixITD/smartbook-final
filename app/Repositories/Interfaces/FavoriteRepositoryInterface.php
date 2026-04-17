<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Favorite\FavoriteDto;
use App\Dto\Favorite\FavoriteFiltersDto;
use App\Dto\Favorite\FavoriteResponseDto;

interface FavoriteRepositoryInterface
{
    /** @return array<FavoriteResponseDto> */
    public function getList(FavoriteFiltersDto $filters): array;

    public function getById(int $id): FavoriteResponseDto|null;

    public function create(FavoriteDto $data): FavoriteResponseDto;

    public function update(int $id, FavoriteDto $data): FavoriteResponseDto|null;

    public function delete(int $id): bool;
}
