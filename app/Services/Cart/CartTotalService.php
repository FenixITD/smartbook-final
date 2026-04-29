<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\CartItem\CartItemWithBookResponseDto;
use App\Dto\PaginatedResponseDto;

final readonly class CartTotalService
{
    public function __construct(
        private GetCartItemsService $getCartItemsService,
    ) {
    }

    public function execute(): float
    {
        $result = $this->getCartItemsService->execute();

        /** @var array<CartItemWithBookResponseDto> $items */
        $items = $result instanceof PaginatedResponseDto ? $result->items : $result;

        return (float) array_sum(
            array_map(
                static fn (CartItemWithBookResponseDto $item): float =>
                    ($item->book?->price ?? 0.0) * $item->quantity,
                $items,
            ),
        );
    }
}
