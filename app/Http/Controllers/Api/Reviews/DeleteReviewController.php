<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reviews;

use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteReviewController
{
    public function __construct(
        private ReviewRepositoryInterface $repository
    ) {}

    public function __invoke(int $review): JsonResponse
    {
        if (! Review::find($review)) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $this->repository->delete($review);

        return response()->json([
            'message' => 'Review deleted successfully',
        ]);
    }
}
