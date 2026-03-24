<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\Http\Resources\Review\ReviewResource;
use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetByIdReviewController
{
    public function __construct(
        private ReviewRepositoryInterface $repository
    ) {}

    public function __invoke(Review $review): JsonResponse
    {
        $reviewId = $this->repository->getById($review->id);

        return (new ReviewResource($reviewId))->response();
    }
}
