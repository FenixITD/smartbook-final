<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Http\Requests\Author\AuthorDataRequest;
use App\Http\Resources\Author\AuthorResource;
use App\Services\Author\CreateAuthorService;
use Illuminate\Http\JsonResponse;

readonly class CreateAuthorController
{
    public function __construct(
        private CreateAuthorService $service
    ) {}

    public function __invoke(AuthorDataRequest $request): JsonResponse
    {
        $author = $this->service->execute($request->toDto());

        return (new AuthorResource($author))->response()->setStatusCode(201);
    }
}
