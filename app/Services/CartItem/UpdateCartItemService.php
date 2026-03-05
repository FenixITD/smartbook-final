<?php

declare(strict_types=1);

namespace App\Services\CartItem;

use App\DTO\CartItem\CartItemDTO;
use App\DTO\CartItem\CartItemResponseDTO;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;

final readonly class UpdateCartItemService
{
    public function __construct(
        private CartItemRepositoryInterface $repository
    ) {}

    public function execute(CartItem $cartItem, CartItemDTO $dto): CartItemResponseDTO
    {
        $this->repository->update($cartItem, [
            'user_id' => $dto->user_id,
            'book_id' => $dto->book_id,
            'quantity' => $dto->quantity,
        ]);

        return CartItemResponseDTO::fromModel($cartItem->fresh());
    }
}
