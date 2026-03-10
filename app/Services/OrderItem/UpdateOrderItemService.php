<?php

declare(strict_types=1);

namespace App\Services\OrderItem;

use App\DTO\OrderItem\OrderItemDTO;
use App\DTO\OrderItem\OrderItemResponseDTO;
use App\Models\OrderItem;
use App\Repositories\Interfaces\OrderItemRepositoryInterface;

final readonly class UpdateOrderItemService
{
    public function __construct(
        private OrderItemRepositoryInterface $repository
    ) {}

    public function execute(OrderItem $orderItem, OrderItemDTO $dto): OrderItemResponseDTO
    {
        $this->repository->update($orderItem, [
            'orderId' => $dto->orderId,
            'bookId' => $dto->bookId,
            'quantity' => $dto->quantity,
            'priceAtPurchase' => $dto->priceAtPurchase,
        ]);

        return OrderItemResponseDTO::fromModel($orderItem->fresh());
    }
}
