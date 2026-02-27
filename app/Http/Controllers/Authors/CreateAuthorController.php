<?php

namespace App\Http\Controllers\Authors;

use App\DTO\AuthorDTO;
use App\Http\Requests\AuthorRequest;
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

        return response()->json([
            'success' => true,
            'author' => $author,
        ], 201);
    }
}
