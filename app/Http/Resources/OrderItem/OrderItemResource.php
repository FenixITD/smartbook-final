<?php

declare(strict_types=1);

namespace App\Http\Resources\OrderItem;

use App\Dto\OrderItem\OrderItemResponseDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OrderItemResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'orderId', type: 'integer', example: 2),
        new OA\Property(property: 'bookId', type: 'integer', example: 3),
        new OA\Property(property: 'quantity', type: 'integer', example: 2),
        new OA\Property(property: 'priceAtPurchase', type: 'string', example: '16.99'),
        new OA\Property(property: 'createdAt', type: 'string', example: '2024-01-01 12:00:00'),
        new OA\Property(property: 'updatedAt', type: 'string', example: '2024-01-01 12:00:00'),
    ],
    type: 'object',
)]
/**
 * @mixin OrderItemResponseDto
 */
final class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var OrderItemResponseDto $resource */
        $resource = $this->resource;

        return [
            'id' => $resource->id,
            'orderId' => $resource->orderId,
            'bookId' => $resource->bookId,
            'quantity' => $resource->quantity,
            'priceAtPurchase' => $resource->priceAtPurchase,
            'createdAt' => $resource->createdAt,
            'updatedAt' => $resource->updatedAt,
        ];
    }
}
