<?php

declare(strict_types=1);

namespace App\Http\Controllers\CartItems;

use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteCartItemController
{
    public function __construct(
        private CartItemRepositoryInterface $repository
    ) {}

    public function __invoke(CartItem $cartItem): JsonResponse
    {
        $this->repository->delete($cartItem);

        return response()->json([
            'message' => 'CartItem deleted successfully',
        ]);
    }
}
