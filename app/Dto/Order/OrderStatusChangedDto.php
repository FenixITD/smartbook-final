<?php

declare(strict_types=1);

namespace App\Dto\Order;

final readonly class OrderStatusChangedDto
{
    public function __construct(
        public int $orderId,
        public string $oldStatus,
        public string $newStatus,
        public string $userEmail,
        public string $userName,
        public string $total,
    ) {}
}
