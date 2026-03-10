<?php

declare(strict_types=1);

namespace App\Http\Controllers\Genres;

use App\DTO\Genre\GenreFiltersDTO;
use App\Http\Requests\Genre\GenreListRequest;
use App\Http\Resources\Genre\GenreCollection;
use App\Repositories\Interfaces\GenreRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListGenreController
{
    public function __construct(
        private GenreRepositoryInterface $repository
    ) {}

    public function __invoke(GenreListRequest $request): JsonResponse
    {
        $filters = GenreFiltersDTO::fromRequest($request);
        $genres = $this->repository->getList($filters);

        return (new GenreCollection($genres))->response();
    }
}
