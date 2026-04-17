<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Http\Requests\Author\AuthorDataRequest;
use App\Http\Resources\Author\AuthorResource;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;

readonly class UpdateAuthorController
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    public function __invoke(AuthorDataRequest $request, int $author): JsonResponse
    {
        $updated = $this->repository->update($author, $request->toDto());

        return (new AuthorResource($updated))->response();
    }
}
