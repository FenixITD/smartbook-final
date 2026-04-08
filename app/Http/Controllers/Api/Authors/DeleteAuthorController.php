<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Authors;

use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteAuthorController
{
    public function __construct(
        private AuthorRepositoryInterface $repository
    ) {}

    public function __invoke(int $author): JsonResponse
    {
        $this->repository->delete($author);

        return response()->json([
            'message' => 'Author deleted successfully',
        ]);
    }
}
