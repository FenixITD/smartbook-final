<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\CartItems;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartItem\CartItemDataRequest;
use App\Http\Resources\CartItem\CartItemResource;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class CreateCartItemController extends Controller
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
    ) {
    }

    #[OA\Post(
        path: '/api/cartItems',
        summary: 'Create cartItem',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/CartItemDataRequest'
            ),
        ),
        tags: ['CartItems'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Get created cartItem',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/CartItemResource'
                )
            ),
        ]
    )]
    public function __invoke(CartItemDataRequest $request): JsonResponse
    {
        $cartItem = $this->repository->create($request->toDto());

        return (new CartItemResource($cartItem))->response()->setStatusCode(201);
    }
}
