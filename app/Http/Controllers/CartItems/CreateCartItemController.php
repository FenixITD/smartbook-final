<?php

declare(strict_types=1);

namespace App\Http\Controllers\CartItems;

use App\DTO\CartItem\CartItemDTO;
use App\Http\Requests\CartItemRequest;
use App\Http\Resources\CartItem\CartItemResource;
use App\Services\CartItem\CreateCartItemService;
use Illuminate\Http\JsonResponse;

readonly class CreateCartItemController
{
    public function __construct(
        private CreateCartItemService $service
    ) {}

    public function __invoke(CartItemRequest $request): JsonResponse
    {
        $dto = CartItemDTO::fromRequest($request);
        $cartItem = $this->service->execute($dto);

        return (new CartItemResource($cartItem))->response()->setStatusCode(201);
    }
}
