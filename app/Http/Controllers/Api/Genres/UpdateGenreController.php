<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Genres;

use App\Http\Requests\Genre\GenreDataRequest;
use App\Http\Resources\Genre\GenreResource;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\JsonResponse;

readonly class UpdateGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository,
    ) {
    }

    public function __invoke(GenreDataRequest $request, int $genreId): JsonResponse
    {
        $updatedGenre = $this->repository->update($genreId, $request->toDto());

        return (new GenreResource($updatedGenre))->response();
    }
}
