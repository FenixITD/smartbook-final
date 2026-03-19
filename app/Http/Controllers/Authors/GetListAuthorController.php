<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authors;

use App\Http\Requests\Author\AuthorListRequest;
use App\Http\Resources\Author\AuthorResource;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListAuthorController
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function __invoke(AuthorListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $authors = $this->repository->getList($filters);

        return AuthorResource::collection($authors)->response();
    }
}
