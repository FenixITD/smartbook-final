<?php

declare(strict_types=1);

namespace App\Dto\Order;

use App\Dto\OrderItem\OrderItemInputDto;

final readonly class OrderDto
{
    /** @param OrderItemInputDto[]|null $items */
    public function __construct(
        public int $userId,
        public string $status,
        public string $shippingAddress,
        public string $paymentMethod,
        public string|null $total = null,
        public array|null $items = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'user_id' => $this->userId,
            'status' => $this->status,
            'shipping_address' => $this->shippingAddress,
            'payment_method' => $this->paymentMethod,
        ];

        if ($this->total !== null) {
            $data['total'] = $this->total;
        }

        return $data;
    }
}
