<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderFiltersDto;
use App\Dto\Order\OrderResponseDto;
use App\Models\Order;
use Tests\TestCase;

class OrderDtoTest extends TestCase
{
    public function test_order_dto_to_array_returns_correct_structure(): void
    {
        $dto = new OrderDto(
            userId: 1,
            total: 99.99,
            status: 'pending',
            shippingAddress: '123 Main St, London',
            paymentMethod: 'credit_card',
        );

        $result = $dto->toArray();

        $this->assertSame([
            'user_id' => 1,
            'total' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St, London',
            'payment_method' => 'credit_card',
        ], $result);
    }

    public function test_order_dto_stores_properties_correctly(): void
    {
        $dto = new OrderDto(
            userId: 5,
            total: 249.50,
            status: 'shipped',
            shippingAddress: '456 Oxford St, London',
            paymentMethod: 'paypal',
        );

        $this->assertSame(5, $dto->userId);
        $this->assertSame(249.50, $dto->total);
        $this->assertSame('shipped', $dto->status);
        $this->assertSame('456 Oxford St, London', $dto->shippingAddress);
        $this->assertSame('paypal', $dto->paymentMethod);
    }

    public function test_order_filters_dto_has_correct_defaults(): void
    {
        $dto = new OrderFiltersDto;

        $this->assertNull($dto->search);
        $this->assertSame(15, $dto->perPage);
        $this->assertSame('id', $dto->sortBy);
        $this->assertSame('asc', $dto->sortDirection);
    }

    public function test_order_filters_dto_accepts_custom_values(): void
    {
        $dto = new OrderFiltersDto(
            search: '42',
            perPage: 25,
            sortBy: 'total',
            sortDirection: 'desc',
        );

        $this->assertSame('42', $dto->search);
        $this->assertSame(25, $dto->perPage);
        $this->assertSame('total', $dto->sortBy);
        $this->assertSame('desc', $dto->sortDirection);
    }

    public function test_order_response_dto_from_model(): void
    {
        $order = new Order;
        $order->id = 10;
        $order->user_id = 3;
        $order->total = 149.99;
        $order->status = 'paid';
        $order->shipping_address = '789 Baker St, London';
        $order->payment_method = 'credit_card';
        $order->created_at = now()->setDateTimeFrom('2024-03-01 09:00:00');
        $order->updated_at = now()->setDateTimeFrom('2024-03-05 15:30:00');

        $dto = OrderResponseDto::fromModel($order);

        $this->assertSame(10, $dto->id);
        $this->assertSame(3, $dto->userId);
        $this->assertSame(149.99, $dto->total);
        $this->assertSame('paid', $dto->status);
        $this->assertSame('789 Baker St, London', $dto->shippingAddress);
        $this->assertSame('credit_card', $dto->paymentMethod);
        $this->assertSame('2024-03-01 09:00:00', $dto->createdAt);
        $this->assertSame('2024-03-05 15:30:00', $dto->updatedAt);
    }

    public function test_order_response_dto_casts_fields_to_correct_types(): void
    {
        $order = new Order;
        $order->id = 1;
        $order->user_id = 2;
        $order->total = 50.00;
        $order->status = 'pending';
        $order->shipping_address = '1 Test Road';
        $order->payment_method = 'cash';
        $order->created_at = now();
        $order->updated_at = now();

        $dto = OrderResponseDto::fromModel($order);

        $this->assertIsInt($dto->userId);
        $this->assertIsFloat($dto->total);
        $this->assertIsString($dto->status);
        $this->assertIsString($dto->shippingAddress);
        $this->assertIsString($dto->paymentMethod);
    }
}
