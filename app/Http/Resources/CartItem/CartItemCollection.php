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
            'cartItems' => CartItemResource::collection($this->collection),
        ];
    }
}
