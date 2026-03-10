<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\DTO\Review\ReviewDTO;
use App\Http\Requests\Review\ReviewDataRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Models\Review;
use App\Services\Review\UpdateReviewService;
use Illuminate\Http\JsonResponse;

readonly class UpdateReviewController
{
    public function __construct(
        private UpdateReviewService $service
    ) {}

    public function __invoke(ReviewDataRequest $request, Review $review): JsonResponse
    {
        $dto = ReviewDTO::fromRequest($request);
        $updated = $this->service->execute($review, $dto);

        return (new ReviewResource($updated))->response();
    }
}
