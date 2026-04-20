<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Http\Resources\Author\AuthorResource;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetAuthorController
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $authorId): JsonResponse
    {
        $author = $this->repository->getById($authorId);

        return (new AuthorResource($author))->response();
    }
}
