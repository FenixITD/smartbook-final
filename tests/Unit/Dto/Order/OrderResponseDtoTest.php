<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Order;

use App\Dto\Order\OrderResponseDto;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class OrderResponseDtoTest extends TestCase
{
    public function test_from_model_creates_dto_with_full_data_and_user_relation(): void
    {
        $user = new User();
        $user->name = 'John Doe';

        $order = new Order();
        $order->id = 10;
        $order->user_id = 5;
        $order->total = 99.99;
        $order->status = 'pending';
        $order->shipping_address = '456 Elm St';
        $order->payment_method = 'paypal';
        $order->created_at = Carbon::parse('2026-06-01 12:00:00');
        $order->updated_at = Carbon::parse('2026-06-01 13:15:00');

        $order->setRelation('user', $user);

        $dto = OrderResponseDto::fromModel($order);

        $this->assertSame(10, $dto->id);
        $this->assertSame(5, $dto->userId);
        $this->assertSame('John Doe', $dto->userName);
        $this->assertSame(99.99, $dto->total);
        $this->assertSame('pending', $dto->status);
        $this->assertSame('456 Elm St', $dto->shippingAddress);
        $this->assertSame('paypal', $dto->paymentMethod);
        $this->assertSame('2026-06-01 12:00:00', $dto->createdAt);
        $this->assertSame('2026-06-01 13:15:00', $dto->updatedAt);
    }

    public function test_from_model_creates_dto_with_null_fields_and_missing_user(): void
    {
        $order = new Order();
        $order->id = 11;
        $order->user_id = 6;
        $order->total = 0.0;
        $order->status = 'cancelled';
        $order->shipping_address = null;
        $order->payment_method = null;
        $order->created_at = null;
        $order->updated_at = null;

        $dto = OrderResponseDto::fromModel($order);

        $this->assertSame(11, $dto->id);
        $this->assertSame('', $dto->userName);
        $this->assertSame('', $dto->shippingAddress);
        $this->assertSame('', $dto->paymentMethod);
        $this->assertSame('', $dto->createdAt);
        $this->assertSame('', $dto->updatedAt);
    }
}
