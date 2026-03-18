<?php

declare(strict_types=1);

namespace App\Services\Genre;

use App\DTO\Genre\GenreDto;
use App\DTO\Genre\GenreResponseDto;
use App\Repositories\Interfaces\GenreRepositoryInterface;

final readonly class CreateGenreService
{
    public function __construct(
        private GenreRepositoryInterface $repository
    ) {}

    public function execute(GenreDto $dto): GenreResponseDto
    {
        return $this->repository->create($dto);
    }
}
