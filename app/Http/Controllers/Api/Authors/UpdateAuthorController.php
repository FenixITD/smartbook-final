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

    public function __invoke(AuthorDataRequest $request, int $authorId): JsonResponse
    {
        $updatedAuthor = $this->repository->update($authorId, $request->toDto());

        return (new AuthorResource($updatedAuthor))->response();
    }
}
