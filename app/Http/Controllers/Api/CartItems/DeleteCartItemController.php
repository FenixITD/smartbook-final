<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\CartItems;

use App\Http\Controllers\Api\Traits\LogsApiActivity;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Delete(
    path: '/api/cartItems/{cartItem}',
    summary: 'Delete cartItem by ID',
    tags: ['CartItems'],
    parameters: [
        new OA\Parameter(
            name: 'cartItem',
            description: 'Delete a single cartItem by ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                type: 'integer',
                example: 3,
            )
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Delete a single cartItem by ID',
            content: [],
        ),
    ]
)]
final readonly class DeleteCartItemController
{
    use LogsApiActivity;

    public function __construct(
        private CartItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $cartItemId): JsonResponse
    {
        $this->repository->delete($cartItemId);

        $this->logActivity('deleted', 'cartItems', $cartItemId);

        return response()->json([
            'message' => 'CartItem deleted successfully',
        ]);
    }
}
