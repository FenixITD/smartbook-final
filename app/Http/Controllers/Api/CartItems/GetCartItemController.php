<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\CartItems;

use App\Http\Resources\CartItem\CartItemResource;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/cartItems/{cartItem}',
    summary: 'Get cartItem by ID',
    tags: ['CartItems'],
    parameters: [
        new OA\Parameter(
            name: 'cartItem',
            description: 'Get a single cartItem by ID',
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
            description: 'Get a single cartItem by ID',
            content: new OA\JsonContent(
                ref: '#/components/schemas/CartItemResource'
            )
        ),
    ]
)]
final readonly class GetCartItemController
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(int $cartItemId): JsonResponse
    {
        $cartItem = $this->repository->getById($cartItemId);

        return (new CartItemResource($cartItem))->response();
    }
}
