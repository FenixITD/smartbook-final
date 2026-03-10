<?php

declare(strict_types=1);

namespace App\Http\Resources\CartItem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class CartItemCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => 'CartItems list retrieved successfully',
            'cartItems' => CartItemResource::collection($this->collection),
            'pagination' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
            ],
        ];
    }
}
