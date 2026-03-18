<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\Genre\GenreDto;
use App\DTO\Genre\GenreFiltersDto;
use App\DTO\Genre\GenreResponseDto;
use App\Models\Genre;

interface GenreRepositoryInterface
{
    /**
     * @return array<GenreResponseDto>
     */
    public function getList(GenreFiltersDto $filters): array;

    public function getById(int $id): ?GenreResponseDto;

    public function create(GenreDto $data): GenreResponseDto;

    public function update(Genre $genre, GenreDto $data): ?GenreResponseDto;

    public function delete(Genre $genre): bool;
}
