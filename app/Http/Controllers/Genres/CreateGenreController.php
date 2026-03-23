<?php

declare(strict_types=1);

namespace App\Http\Controllers\Genres;

use App\Http\Requests\Genre\GenreDataRequest;
use App\Http\Resources\Genre\GenreResource;
use App\Services\Genre\CreateGenreService;
use Illuminate\Http\JsonResponse;

readonly class CreateGenreController
{
    public function __construct(
        private CreateGenreService $service
    ) {}

    public function __invoke(GenreDataRequest $request): JsonResponse
    {
        $genre = $this->service->execute($request->toDto());

        return (new GenreResource($genre))->response()->setStatusCode(201);
    }
}
