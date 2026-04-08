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
        private GenreRepositoryInterface $repository
    ) {}

    public function __invoke(GenreDataRequest $request, int $genre): JsonResponse
    {
        $updated = $this->repository->update($genre, $request->toDto());

        return (new GenreResource($updated))->response();
    }
}
