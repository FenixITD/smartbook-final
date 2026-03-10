<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\DTO\Genre\GenreFiltersDTO;
use App\DTO\Genre\GenreResponseDTO;
use App\Models\Genre;

interface GenreRepositoryInterface
{
    /**
     * @return array<GenreResponseDTO>
     */
    public function getList(GenreFiltersDTO $filters): array;

    public function getById(int $id): ?GenreResponseDTO;

    public function create(array $data): GenreResponseDTO;

    public function update(Genre $genre, array $data): ?GenreResponseDTO;

    public function delete(Genre $genre): bool;
}
