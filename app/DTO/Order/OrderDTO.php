<?php

declare(strict_types=1);

namespace App\DTO\Order;

use App\Http\Requests\Order\OrderDataRequest;

final readonly class OrderDTO
{
    public function __construct(
        public int $userId,
        public float $total,
        public string $status,
        public string $shippingAddress,
        public string $paymentMethod,
    ) {}

    public static function fromRequest(OrderDataRequest $request): self
    {
        return new self(
            userId: $request->integer('userId'),
            total: $request->float('total'),
            status: (string) $request->string('status', 'pending'),
            shippingAddress: (string) $request->string('shippingAddress'),
            paymentMethod: (string) $request->string('paymentMethod'),
        );
    }
}
