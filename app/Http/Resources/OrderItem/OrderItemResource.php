<?php

declare(strict_types=1);

namespace App\Http\Resources\OrderItem;

use App\Dto\OrderItem\OrderItemResponseDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
