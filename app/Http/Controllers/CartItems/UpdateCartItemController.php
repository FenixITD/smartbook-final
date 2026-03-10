<?php

declare(strict_types=1);

namespace App\Http\Controllers\CartItems;

use App\DTO\CartItem\CartItemDTO;
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
        $dto = CartItemDTO::fromRequest($request);
        $updated = $this->service->execute($cartItem, $dto);

        return (new CartItemResource($updated))->response();
    }
}
