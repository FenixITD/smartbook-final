<?php

declare(strict_types=1);

namespace App\Http\Controllers\CartItems;

use App\Http\Requests\CartItem\CartItemDataRequest;
use App\Http\Resources\CartItem\CartItemResource;
use App\Models\CartItem;
use App\Services\CartItem\UpdateCartItemService;
use Illuminate\Http\JsonResponse;

readonly class UpdateCartItemController
{
    public function __construct(
        private UpdateCartItemService $service
    ) {}

    public function __invoke(CartItemDataRequest $request, CartItem $cartItem): JsonResponse
    {
        $updated = $this->service->execute($cartItem, $request->toDto());

        return (new CartItemResource($updated))->response();
    }
}
