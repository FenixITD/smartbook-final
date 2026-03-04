<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authors;

use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteAuthorController
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function __invoke(Author $author): JsonResponse
    {
        $this->repository->delete($author);

        return response()->json([
            'message' => 'Author deleted successfully',
        ]);
    }
}
