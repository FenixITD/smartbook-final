<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authors;

use App\DTO\Author\AuthorDTO;
use App\Http\Requests\AuthorRequest;
use App\Http\Responses\CreateAuthorResponse;
use App\Services\Author\CreateAuthorService;
use Illuminate\Http\JsonResponse;

readonly class CreateAuthorController
{
    public function __construct(
        private CreateAuthorService $service
    ) {}

    public function __invoke(AuthorRequest $request): JsonResponse
    {
        $dto = AuthorDTO::fromRequest($request);
        $author = $this->service->execute($dto);

        return new CreateAuthorResponse($author);
    }
}
