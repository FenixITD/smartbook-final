<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reviews;

use App\Http\Requests\Review\ReviewDataRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;

readonly class CreateReviewController
{
    public function __construct(
        private ReviewRepositoryInterface $repository
    ) {}

    public function __invoke(ReviewDataRequest $request): JsonResponse
    {
        $review = $this->repository->create($request->toDto());

        return (new ReviewResource($review))->response()->setStatusCode(201);
    }
}
