<?php

declare(strict_types=1);

namespace App\Http\Controllers\CartItems;

use App\Http\Resources\CartItem\CartItemResource;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetByIdCartItemController
{
    public function __construct(
        private CartItemRepositoryInterface $repository
    ) {}

    public function __invoke(CartItem $cartItem): JsonResponse
    {
        $cartItemId = $this->repository->getById($cartItem->id);

        return (new CartItemResource($cartItemId))->response();
    }
}
