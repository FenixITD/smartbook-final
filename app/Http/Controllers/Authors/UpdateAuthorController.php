<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authors;

use App\Http\Requests\Author\AuthorDataRequest;
use App\Http\Resources\Author\AuthorResource;
use App\Models\Author;
use App\Services\Author\UpdateAuthorService;
use Illuminate\Http\JsonResponse;

readonly class UpdateAuthorController
{
    public function __construct(
        private UpdateAuthorService $service
    ) {}

    public function __invoke(AuthorDataRequest $request, Author $author): JsonResponse
    {
        $updated = $this->service->execute($author, $request->toDto());

        return (new AuthorResource($updated))->response();
    }
}
