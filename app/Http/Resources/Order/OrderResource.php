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
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'userId', type: 'integer', example: 3),
        new OA\Property(property: 'total', type: 'string', example: '196.54'),
        new OA\Property(property: 'status', type: 'string', example: 'delivered'),
        new OA\Property(property: 'shippingAddress', type: 'string', example: 'Pushkina 19'),
        new OA\Property(property: 'paymentMethod', type: 'string', example: 'cash'),
        new OA\Property(property: 'createdAt', type: 'string', example: '2024-01-01 12:00:00'),
        new OA\Property(property: 'updatedAt', type: 'string', example: '2024-01-01 12:00:00'),
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
        /** @var OrderResponseDto $resource */
        $resource = $this->resource;

        return [
            'id' => $resource->id,
            'userId' => $resource->userId,
            'total' => $resource->total,
            'status' => $resource->status,
            'shippingAddress' => $resource->shippingAddress,
            'paymentMethod' => $resource->paymentMethod,
            'createdAt' => $resource->createdAt,
            'updatedAt' => $resource->updatedAt,
        ];
    }
}
