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
        new OA\Property(property: 'orderId', type: 'integer', example: 2),
        new OA\Property(property: 'bookId', type: 'integer', example: 3),
        new OA\Property(property: 'quantity', type: 'integer', example: 2),
        new OA\Property(property: 'priceAtPurchase', type: 'number', example: 16.99),
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
        return [
            'id' => $this->id,
            'orderId' => $this->orderId,
            'bookId' => $this->bookId,
            'quantity' => $this->quantity,
            'priceAtPurchase' => $this->priceAtPurchase,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
