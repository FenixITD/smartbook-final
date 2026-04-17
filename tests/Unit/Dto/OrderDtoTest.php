<?php

declare(strict_types=1);

namespace Tests\Unit\Dto;

use App\Dto\Order\OrderDto;
use App\Dto\Order\OrderFiltersDto;
use App\Dto\Order\OrderResponseDto;
use App\Models\Order;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class OrderDtoTest extends TestCase
{
    public function testOrderDtoToArrayReturnsCorrectStructure(): void
    {
        $dto = new OrderDto(
            userId: 1,
            total: 99.99,
            status: 'pending',
            shippingAddress: '123 Main St, London',
            paymentMethod: 'credit_card',
        );

        $result = $dto->toArray();

        self::assertSame([
            'user_id' => 1,
            'total' => 99.99,
            'status' => 'pending',
            'shipping_address' => '123 Main St, London',
            'payment_method' => 'credit_card',
        ], $result);
    }

    public function testOrderDtoStoresPropertiesCorrectly(): void
    {
        $dto = new OrderDto(
            userId: 5,
            total: 249.50,
            status: 'shipped',
            shippingAddress: '456 Oxford St, London',
            paymentMethod: 'paypal',
        );

        self::assertSame(5, $dto->userId);
        self::assertSame(249.50, $dto->total);
        self::assertSame('shipped', $dto->status);
        self::assertSame('456 Oxford St, London', $dto->shippingAddress);
        self::assertSame('paypal', $dto->paymentMethod);
    }

    public function testOrderFiltersDtoHasCorrectDefaults(): void
    {
        $dto = new OrderFiltersDto();

        self::assertNull($dto->search);
        self::assertSame(15, $dto->perPage);
        self::assertSame('id', $dto->sortBy);
        self::assertSame('asc', $dto->sortDirection);
    }

    public function testOrderFiltersDtoAcceptsCustomValues(): void
    {
        $dto = new OrderFiltersDto(
            search: '42',
            perPage: 25,
            sortBy: 'total',
            sortDirection: 'desc',
        );

        self::assertSame('42', $dto->search);
        self::assertSame(25, $dto->perPage);
        self::assertSame('total', $dto->sortBy);
        self::assertSame('desc', $dto->sortDirection);
    }

    public function testOrderResponseDtoFromModel(): void
    {
        $order = new Order();
        $order->id = 10;
        $order->user_id = 3;
        $order->total = 149.99;
        $order->status = 'paid';
        $order->shipping_address = '789 Baker St, London';
        $order->payment_method = 'credit_card';
        $order->created_at = now()->setDateTimeFrom('2024-03-01 09:00:00');
        $order->updated_at = now()->setDateTimeFrom('2024-03-05 15:30:00');

        $dto = OrderResponseDto::fromModel($order);

        self::assertSame(10, $dto->id);
        self::assertSame(3, $dto->userId);
        self::assertSame(149.99, $dto->total);
        self::assertSame('paid', $dto->status);
        self::assertSame('789 Baker St, London', $dto->shippingAddress);
        self::assertSame('credit_card', $dto->paymentMethod);
        self::assertSame('2024-03-01 09:00:00', $dto->createdAt);
        self::assertSame('2024-03-05 15:30:00', $dto->updatedAt);
    }

    public function testOrderResponseDtoCastsFieldsToCorrectTypes(): void
    {
        $order = new Order();
        $order->id = 1;
        $order->user_id = 2;
        $order->total = 50.00;
        $order->status = 'pending';
        $order->shipping_address = '1 Test Road';
        $order->payment_method = 'cash';
        $order->created_at = now();
        $order->updated_at = now();

        $dto = OrderResponseDto::fromModel($order);

        self::assertIsInt($dto->userId);
        self::assertIsFloat($dto->total);
        self::assertIsString($dto->status);
        self::assertIsString($dto->shippingAddress);
        self::assertIsString($dto->paymentMethod);
    }
}
