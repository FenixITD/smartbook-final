<?php

declare(strict_types=1);

namespace App\Services\Genre;

use App\DTO\Genre\GenreDto;
use App\DTO\Genre\GenreResponseDto;
use App\Models\Genre;
use App\Repositories\Interfaces\GenreRepositoryInterface;

final readonly class UpdateGenreService
{
    public function __construct(
        private GenreRepositoryInterface $repository
    ) {}

    public function execute(Genre $genre, GenreDto $dto): GenreResponseDto
    {
        return $this->repository->update($genre, $dto);
    }
}
