<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Http\Resources\Author\AuthorResource;
use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetAuthorController
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function __invoke(Author $author): JsonResponse
    {
        $authorId = $this->repository->getById($author->id);

        return (new AuthorResource($authorId))->response();
    }
}
