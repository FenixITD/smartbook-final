<?php

declare(strict_types=1);

namespace App\Dto\Order;

final readonly class OrderDTO
{
    public function __construct(
        public int $userId,
        public float $total,
        public string $status,
        public string $shippingAddress,
        public string $paymentMethod,
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'total' => $this->total,
            'status' => $this->status,
            'shipping_address' => $this->shippingAddress,
            'payment_method' => $this->paymentMethod,
        ];
    }
}
