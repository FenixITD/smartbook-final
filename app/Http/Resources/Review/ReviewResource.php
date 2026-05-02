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
        new OA\Property(property: 'userId', type: 'integer', example: 3),
        new OA\Property(property: 'bookId', type: 'integer', example: 4),
        new OA\Property(property: 'rating', type: 'number', example: 4.9),
        new OA\Property(property: 'comment', type: 'string', example: 'Good comment'),
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
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'bookId' => $this->bookId,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
