<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Order;

use App\Dto\Order\OrderStatusChangedDto;
use Tests\TestCase;

final class OrderStatusChangedDtoTest extends TestCase
{
    public function test_order_status_changed_dto_initializes_correctly(): void
    {
        $dto = new OrderStatusChangedDto(
            orderId: 100,
            oldStatus: 'pending',
            newStatus: 'completed',
            userEmail: 'alex@example.com',
            userName: 'Alex Mercer',
            total: 1250.00,
        );

        $this->assertSame(100, $dto->orderId);
        $this->assertSame('pending', $dto->oldStatus);
        $this->assertSame('completed', $dto->newStatus);
        $this->assertSame('alex@example.com', $dto->userEmail);
        $this->assertSame('Alex Mercer', $dto->userName);
        $this->assertSame(1250.00, $dto->total);
    }
}
