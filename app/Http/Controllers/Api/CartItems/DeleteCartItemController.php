<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\CartItems;

use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteCartItemController
{
    public function __construct(
        private CartItemRepositoryInterface $repository
    ) {}

    public function __invoke(int $cartItem): JsonResponse
    {
        if (! CartItem::find($cartItem)) {
            return response()->json(['message' => 'CartItem not found'], 404);
        }

        $this->repository->delete($cartItem);

        return response()->json([
            'message' => 'CartItem deleted successfully',
        ]);
    }
}
