<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authors;

use App\Http\Responses\DeleteAuthorResponse;
use App\Models\Author;
use App\Services\Author\DeleteAuthorService;
use Illuminate\Http\JsonResponse;

readonly class DeleteAuthorController
{
    public function __construct(
        private DeleteAuthorService $service
    ) {}

    public function __invoke(Author $author): JsonResponse
    {
        $this->service->execute($author);

        return new DeleteAuthorResponse;
    }
}
