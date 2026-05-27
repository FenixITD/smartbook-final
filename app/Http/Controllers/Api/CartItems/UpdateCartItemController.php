<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\CartItems;

use App\Http\Requests\CartItem\CartItemDataRequest;
use App\Http\Resources\CartItem\CartItemResource;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/api/cartItems/{cartItem}',
    summary: 'Update cartItem by ID',
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            ref: '#/components/schemas/CartItemDataRequest'
        ),
    ),
    tags: ['CartItems'],
    parameters: [
        new OA\Parameter(
            name: 'cartItem',
            description: 'Update a single cartItem by ID',
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
            description: 'Update a single cartItem by ID',
            content: new OA\JsonContent(
                ref: '#/components/schemas/CartItemResource'
            )
        ),
    ]
)]
readonly class UpdateCartItemController
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(CartItemDataRequest $request, int $cartItemId): JsonResponse
    {
        $updatedCartItem = $this->repository->update($cartItemId, $request->toDto());

        return (new CartItemResource($updatedCartItem))->response();
    }
}
