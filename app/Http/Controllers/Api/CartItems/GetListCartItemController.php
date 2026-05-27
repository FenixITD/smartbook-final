<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\CartItems;

use App\Http\Requests\CartItem\CartItemListRequest;
use App\Http\Resources\CartItem\CartItemResource;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/cartItems',
    summary: 'Get a list of all cartItems',
    security: [['bearerAuth' => []]],
    tags: ['CartItems'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get a list of all cartItems',
            content: new OA\JsonContent(
                ref: '#/components/schemas/CartItemResource'
            )
        ),
    ]
)]
final readonly class GetListCartItemController
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
    ) {
    }

    public function __invoke(CartItemListRequest $request): JsonResponse
    {
        $filters = $request->toDto();
        $cartItems = $this->repository->getList($filters);

        return CartItemResource::collection($cartItems)->response();
    }
}
