<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Order;

use App\Dto\Order\OrderDto;
use Tests\TestCase;

final class OrderDtoTest extends TestCase
{
    public function test_order_dto_initializes_and_returns_array(): void
    {
        $dto = new OrderDto(
            userId: 1,
            total: 250.50,
            status: 'pending',
            shippingAddress: '123 Main St, Springfield',
            paymentMethod: 'credit_card',
        );

        $this->assertSame(1, $dto->userId);
        $this->assertSame(250.50, $dto->total);
        $this->assertSame('pending', $dto->status);
        $this->assertSame('123 Main St, Springfield', $dto->shippingAddress);
        $this->assertSame('credit_card', $dto->paymentMethod);

        $this->assertSame([
            'user_id' => 1,
            'total' => 250.50,
            'status' => 'pending',
            'shipping_address' => '123 Main St, Springfield',
            'payment_method' => 'credit_card',
        ], $dto->toArray());
    }
}
