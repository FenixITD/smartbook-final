<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reviews;

use App\Http\Controllers\Api\Traits\LogsApiActivity;
use App\Http\Requests\Review\ReviewDataRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/reviews',
    summary: 'Create review',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/ReviewDataRequest'
        ),
    ),
    tags: ['Reviews'],
    responses: [
        new OA\Response(
            response: 201,
            description: 'Get created review',
            content: new OA\JsonContent(
                ref: '#/components/schemas/ReviewResource'
            )
        ),
    ]
)]
readonly class CreateReviewController
{
    use LogsApiActivity;

    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function __invoke(ReviewDataRequest $request): JsonResponse
    {
        $review = $this->repository->create($request->toDto());

        $this->logActivity('created', 'reviews', $review->id, $request->validated());

        return (new ReviewResource($review))->response()->setStatusCode(201);
    }
}
