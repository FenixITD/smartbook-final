<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authors;

use App\DTO\AuthorDTO;
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
        $filters = AuthorDTO::filtersFromRequest($request);
        $authors = $this->service->execute($filters);

        return response()->json([
            'authors' => $authors,
        ]);
    }
}
