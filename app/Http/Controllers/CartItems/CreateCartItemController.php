<?php

declare(strict_types=1);

namespace App\Http\Controllers\CartItems;

use App\Http\Requests\CartItem\CartItemDataRequest;
use App\Http\Resources\CartItem\CartItemResource;
use App\Services\CartItem\CreateCartItemService;
use Illuminate\Http\JsonResponse;

readonly class CreateCartItemController
{
    public function __construct(
        private CreateCartItemService $service
    ) {}

    public function __invoke(CartItemDataRequest $request): JsonResponse
    {
        $cartItem = $this->service->execute($request->toDto());

        return (new CartItemResource($cartItem))->response()->setStatusCode(201);
    }
}
