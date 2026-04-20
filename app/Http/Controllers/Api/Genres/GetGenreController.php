<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Genres;

use App\Http\Resources\Genre\GenreResource;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $genreId): JsonResponse
    {
        $genre = $this->repository->getById($genreId);

        return (new GenreResource($genre))->response();
    }
}
