<?php

declare(strict_types=1);

namespace App\Http\Resources\Order;

use App\Dto\Order\OrderResponseDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'OrderResource',
    properties: [
        new OA\Property(property: 'userId', type: 'integer', example: 3),
        new OA\Property(property: 'total', type: 'number', example: 196.54),
        new OA\Property(property: 'status', type: 'string', example: 'delivered'),
        new OA\Property(property: 'shippingAddress', type: 'string', example: 'Pushkina 19'),
        new OA\Property(property: 'paymentMethod', type: 'string', example: 'cash'),
    ],
    type: 'object',
)]
/**
 * @mixin OrderResponseDto
 */
final class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'total' => $this->total,
            'status' => $this->status,
            'shippingAddress' => $this->shippingAddress,
            'paymentMethod' => $this->paymentMethod,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
