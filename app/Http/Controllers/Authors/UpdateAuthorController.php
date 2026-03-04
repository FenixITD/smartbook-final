<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authors;

use App\DTO\Author\AuthorDTO;
use App\Http\Requests\AuthorRequest;
use App\Http\Resources\Author\AuthorResource;
use App\Models\Author;
use App\Services\Author\UpdateAuthorService;
use Illuminate\Http\JsonResponse;

readonly class UpdateAuthorController
{
    public function __construct(
        private UpdateAuthorService $service
    ) {}

    public function __invoke(AuthorRequest $request, Author $author): JsonResponse
    {
        $dto = AuthorDTO::fromRequest($request);
        $updated = $this->service->execute($author, $dto);

        return (new AuthorResource($updated))->response();
    }
}
