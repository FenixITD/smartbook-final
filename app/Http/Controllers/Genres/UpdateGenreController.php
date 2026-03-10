<?php

declare(strict_types=1);

namespace App\Http\Controllers\Genres;

use App\DTO\Genre\GenreDTO;
use App\Http\Requests\Genre\GenreDataRequest;
use App\Http\Resources\Genre\GenreResource;
use App\Models\Genre;
use App\Services\Genre\UpdateGenreService;
use Illuminate\Http\JsonResponse;

readonly class UpdateGenreController
{
    public function __construct(
        private UpdateGenreService $service
    ) {}

    public function __invoke(GenreDataRequest $request, Genre $genre): JsonResponse
    {
        $dto = GenreDTO::fromRequest($request);
        $updated = $this->service->execute($genre, $dto);

        return (new GenreResource($updated))->response();
    }
}
