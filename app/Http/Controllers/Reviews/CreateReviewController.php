<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\DTO\Review\ReviewDTO;
use App\Http\Requests\Review\ReviewDataRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Services\Review\CreateReviewService;
use Illuminate\Http\JsonResponse;

readonly class CreateReviewController
{
    public function __construct(
        private CreateReviewService $service
    ) {}

    public function __invoke(ReviewDataRequest $request): JsonResponse
    {
        $dto = ReviewDTO::fromRequest($request);
        $review = $this->service->execute($dto);

        return (new ReviewResource($review))->response()->setStatusCode(201);
    }
}
