<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reviews;

use App\Http\Resources\Review\ReviewResource;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/reviews/{review}',
    summary: 'Get review by ID',
    security: [['bearerAuth' => []]],
    tags: ['Reviews'],
    parameters: [
        new OA\Parameter(
            name: 'review',
            description: 'Get a single review by ID',
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
            description: 'Get a single review by ID',
            content: new OA\JsonContent(
                ref: '#/components/schemas/ReviewResource'
            )
        ),
    ]
)]
final readonly class GetReviewController
{
    public function __construct(
        private ReviewRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $reviewId): JsonResponse
    {
        $review = $this->repository->getById($reviewId);

        return (new ReviewResource($review))->response();
    }
}
