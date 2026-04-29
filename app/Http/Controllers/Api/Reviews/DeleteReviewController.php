<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reviews;

use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteReviewController
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $reviewId): JsonResponse
    {
        $this->repository->delete($reviewId);

        return response()->json([
            'message' => 'Review deleted successfully',
        ]);
    }
}
