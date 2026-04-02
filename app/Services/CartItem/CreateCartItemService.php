<?php

declare(strict_types=1);

namespace App\Services\CartItem;

use App\DTO\CartItem\CartItemDto;
use App\DTO\CartItem\CartItemResponseDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;

final readonly class CreateCartItemService
{
    public function __construct(
        private CartItemRepositoryInterface $repository
    ) {}

    public function execute(CartItemDto $dto): CartItemResponseDto
    {
        return $this->repository->create($dto);
    }
}
