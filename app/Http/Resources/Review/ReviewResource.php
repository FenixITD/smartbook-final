<?php

declare(strict_types=1);

namespace App\Http\Resources\Review;

use App\Dto\Review\ReviewResponseDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ReviewResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'userId', type: 'integer', example: 3),
        new OA\Property(property: 'bookId', type: 'integer', example: 4),
        new OA\Property(property: 'rating', type: 'number', example: 4.9),
        new OA\Property(property: 'comment', type: 'string', example: 'Good comment'),
        new OA\Property(property: 'createdAt', type: 'string', example: '2024-01-01 12:00:00'),
        new OA\Property(property: 'updatedAt', type: 'string', example: '2024-01-01 12:00:00'),
    ],
    type: 'object',
)]
/**
 * @mixin ReviewResponseDto
 */
final class ReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ReviewResponseDto $resource */
        $resource = $this->resource;

        return [
            'id' => $resource->id,
            'userId' => $resource->userId,
            'bookId' => $resource->bookId,
            'rating' => $resource->rating,
            'comment' => $resource->comment,
            'createdAt' => $resource->createdAt,
            'updatedAt' => $resource->updatedAt,
        ];
    }
}
