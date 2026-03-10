<?php

declare(strict_types=1);

namespace App\Services\Genre;

use App\DTO\Genre\GenreDTO;
use App\DTO\Genre\GenreResponseDTO;
use App\Models\Genre;
use App\Repositories\Interfaces\GenreRepositoryInterface;

final readonly class UpdateGenreService
{
    public function __construct(
        private GenreRepositoryInterface $repository
    ) {}

    public function execute(Genre $genre, GenreDTO $dto): GenreResponseDTO
    {
        $this->repository->update($genre, [
            'name' => $dto->name,
            'slug' => $dto->slug ?? str($dto->name)->slug(),
            'description' => $dto->description,
        ]);

        return GenreResponseDTO::fromModel($genre->fresh());
    }
}
