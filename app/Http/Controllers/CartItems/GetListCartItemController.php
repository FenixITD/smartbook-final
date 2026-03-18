<?php

declare(strict_types=1);

namespace App\Http\Controllers\CartItems;

use App\DTO\CartItem\CartItemFiltersDTO;
use App\Http\Requests\CartItem\CartItemListRequest;
use App\Http\Resources\CartItem\CartItemCollection;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\JsonResponse;

final readonly class GetListCartItemController
{
    public function __construct(
        private CartItemRepositoryInterface $repository
    ) {}

    public function __invoke(CartItemListRequest $request): JsonResponse
    {
        $filters = CartItemFiltersDTO::fromRequest($request);
        $cartItems = $this->repository->getList($filters);

        return (new CartItemCollection($cartItems))->response();
    }
}
