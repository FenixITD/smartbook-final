<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Reviews;

use App\Repositories\Interfaces\ReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/reviews/{review}',
    summary: 'Delete review by ID',
    security: [['bearerAuth' => []]],
    tags: ['Reviews'],
    parameters: [
        new OA\Parameter(
            name: 'review',
            description: 'Delete a single review by ID',
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
            description: 'Delete a single review by ID',
            content: [],
        ),
    ]
)]
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
