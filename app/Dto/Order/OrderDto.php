<?php

declare(strict_types=1);

namespace App\Dto\Order;

final readonly class OrderDto
{
    public function __construct(
        public int $userId,
        public string $status,
        public string $shippingAddress,
        public string $paymentMethod,
        public float|null $total = null,
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
