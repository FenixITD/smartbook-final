<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reviews;

use App\Http\Controllers\Api\Traits\LogsApiActivity;
use App\Http\Requests\Review\ReviewDataRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/api/reviews/{review}',
    summary: 'Update review by ID',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/ReviewDataRequest'
        ),
    ),
    tags: ['Reviews'],
    parameters: [
        new OA\Parameter(
            name: 'review',
            description: 'Update a single review by ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
                example: 3,
            )
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update a single review by ID',
            content: new OA\JsonContent(
                ref: '#/components/schemas/ReviewResource'
            )
        ),
    ]
)]
readonly class UpdateReviewController
{
    use LogsApiActivity;

    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function __invoke(ReviewDataRequest $request, int $reviewId): JsonResponse
    {
        $updatedReview = $this->repository->update($reviewId, $request->toDto());

        $this->logActivity('updated', 'reviews', $reviewId, $request->validated());

        return (new ReviewResource($updatedReview))->response();
    }
}
