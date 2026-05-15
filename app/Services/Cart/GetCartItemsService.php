<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\CartItem\CartItemWithBookResponseDto;

final readonly class GetCartItemsService
{
    public function __construct(
        private CartResolverService $cartResolverService,
    ) {
    }

    /**
     * @return array<CartItemWithBookResponseDto>
     *
     * Retrieves all items from the current active cart along with their associated detailed book data.
     */
    /** @return array<CartItemWithBookResponseDto> */
    public function execute(): array
    {
        return $this->cartResolverService->resolve()->getItems();
    }
}
