<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\CartItems;

use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class DeleteCartItemController
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $cartItemId): JsonResponse
    {
        if ($this->repository->getById($cartItemId) === null) {
            return response()->json(['message' => 'CartItem not found'], 404);
        }

        $this->repository->delete($cartItemId);

        return response()->json([
            'message' => 'CartItem deleted successfully',
        ]);
    }
}
