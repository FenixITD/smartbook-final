<?php

namespace App\Http\Controllers\Authors;

use App\Http\Requests\AuthorRequest;
use App\Services\Author\GetListAuthorService;
use Illuminate\Http\JsonResponse;

readonly class GetListAuthorController
{
    public function __construct(
        private GetListAuthorService $service
    ) {}

    public function __invoke(AuthorRequest $request): JsonResponse
    {
        $authors = $this->service->execute($request);

        return response()->json([
            'success' => true,
            'authors' => $authors,
        ]);
    }
}
