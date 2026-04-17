<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\CartItems;

use App\Http\Requests\CartItem\CartItemDataRequest;
use App\Http\Resources\CartItem\CartItemResource;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

readonly class UpdateCartItemController
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(CartItemDataRequest $request, int $cartItem): JsonResponse
    {
        $updated = $this->repository->update($cartItem, $request->toDto());

        return (new CartItemResource($updated))->response();
    }
}
