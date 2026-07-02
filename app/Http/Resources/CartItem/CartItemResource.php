<?php

declare(strict_types=1);

namespace App\Http\Resources\CartItem;

use App\Dto\CartItem\CartItemResponseDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CartItemResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'userId', type: 'integer', example: 3),
        new OA\Property(property: 'bookId', type: 'integer', example: 4),
        new OA\Property(property: 'quantity', type: 'integer', example: 13),
        new OA\Property(property: 'createdAt', type: 'string', example: '2024-01-01 12:00:00'),
        new OA\Property(property: 'updatedAt', type: 'string', example: '2024-01-01 12:00:00'),
    ],
    type: 'object',
)]
/**
 * @mixin CartItemResponseDto
 */
final class CartItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var CartItemResponseDto $resource */
        $resource = $this->resource;

        return [
            'id' => $resource->id,
            'userId' => $resource->userId,
            'bookId' => $resource->bookId,
            'quantity' => $resource->quantity,
            'createdAt' => $resource->createdAt,
            'updatedAt' => $resource->updatedAt,
        ];
    }
}
