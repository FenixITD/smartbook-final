<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authors;

use App\DTO\Author\AuthorFiltersDTO;
use App\Http\Requests\AuthorRequest;
use App\Http\Resources\AuthorCollection;
use App\Services\Author\ListAuthorsService;
use Illuminate\Http\JsonResponse;

final readonly class GetListAuthorController
{
    public function __construct(
        private ListAuthorsService $service
    ) {}

    public function __invoke(AuthorRequest $request): JsonResponse
    {
        $filters = AuthorFiltersDTO::fromRequest($request);
        $authors = $this->service->execute($filters);

        return (new AuthorCollection($authors))->response();
    }
}
