<?php

declare(strict_types=1);

namespace App\Services\Genre;

use App\DTO\Genre\GenreDTO;
use App\DTO\Genre\GenreResponseDTO;
use App\Repositories\Interfaces\GenreRepositoryInterface;

final readonly class CreateGenreService
{
    public function __construct(
        private GenreRepositoryInterface $repository
    ) {}

    public function execute(GenreDTO $dto): GenreResponseDTO
    {
        return $this->repository->create([
            'name' => $dto->name,
            'slug' => $dto->slug ?? str($dto->name)->slug(),
            'description' => $dto->description,
        ]);
    }
}
