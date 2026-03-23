<?php

declare(strict_types=1);

namespace App\Http\Controllers\Genres;

use App\Http\Resources\Genre\GenreResource;
use App\Models\Genre;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetByIdGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository
    ) {}

    public function __invoke(Genre $genre): JsonResponse
    {
        $genreId = $this->repository->getById($genre->id);

        return (new GenreResource($genreId))->response();
    }
}
