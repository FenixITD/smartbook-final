<?php

declare(strict_types=1);

namespace App\Services\OrderItem;

use App\DTO\OrderItem\OrderItemDTO;
use App\DTO\OrderItem\OrderItemResponseDTO;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;

final readonly class CreateOrderItemService
{
    public function __construct(
        private OrderItemRepositoryInterface $repository
    ) {}

    public function execute(OrderItemDTO $dto): OrderItemResponseDTO
    {
        return $this->repository->create([
            'orderId' => $dto->orderId,
            'bookId' => $dto->bookId,
            'quantity' => $dto->quantity,
            'priceAtPurchase' => $dto->priceAtPurchase,
        ]);
    }
}
