<?php

declare(strict_types=1);

namespace App\Services\CartItem;

use App\DTO\CartItem\CartItemDTO;
use App\DTO\CartItem\CartItemResponseDTO;
use App\Repositories\Interfaces\CartItemRepositoryInterface;

final readonly class CreateCartItemService
{
    public function __construct(
        private CartItemRepositoryInterface $repository
    ) {}

    public function execute(CartItemDTO $dto): CartItemResponseDTO
    {
        return $this->repository->create([
            'user_id' => $dto->user_id,
            'book_id' => $dto->book_id,
            'quantity' => $dto->quantity,
        ]);
    }
}
