<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authors;

use App\DTO\Author\AuthorFiltersDTO;
use App\Http\Requests\Author\AuthorListRequest;
use App\Http\Resources\Author\AuthorCollection;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListAuthorController
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function __invoke(AuthorListRequest $request): JsonResponse
    {
        $filters = AuthorFiltersDTO::fromRequest($request);
        $authors = $this->repository->getList($filters);

        return (new AuthorCollection($authors))->response();
    }
}
