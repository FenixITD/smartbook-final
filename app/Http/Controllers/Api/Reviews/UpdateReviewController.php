<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reviews;

use App\Http\Requests\Review\ReviewDataRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;

readonly class UpdateReviewController
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function __invoke(ReviewDataRequest $request, int $reviewId): JsonResponse
    {
        $updatedReview = $this->repository->update($reviewId, $request->toDto());

        return (new ReviewResource($updatedReview))->response();
    }
}
