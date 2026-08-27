<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\OrderStatusEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class OrderStatusEnumTest extends TestCase
{
    public function test_pending_transitions_to_paid(): void
    {
        $this->assertTrue(OrderStatusEnum::Pending->canTransitionTo(OrderStatusEnum::Paid));
    }

    public function test_pending_transitions_to_cancelled(): void
    {
        $this->assertTrue(OrderStatusEnum::Pending->canTransitionTo(OrderStatusEnum::Cancelled));
    }

    #[DataProvider('pendingInvalidTransitionProvider')]
    public function test_pending_rejects_invalid_transitions(OrderStatusEnum $target): void
    {
        $this->assertFalse(OrderStatusEnum::Pending->canTransitionTo($target));
    }

    public function test_paid_transitions_to_shipped(): void
    {
        $this->assertTrue(OrderStatusEnum::Paid->canTransitionTo(OrderStatusEnum::Shipped));
    }

    public function test_paid_transitions_to_cancelled(): void
    {
        $this->assertTrue(OrderStatusEnum::Paid->canTransitionTo(OrderStatusEnum::Cancelled));
    }

    #[DataProvider('paidInvalidTransitionProvider')]
    public function test_paid_rejects_invalid_transitions(OrderStatusEnum $target): void
    {
        $this->assertFalse(OrderStatusEnum::Paid->canTransitionTo($target));
    }

    public function test_shipped_transitions_to_delivered(): void
    {
        $this->assertTrue(OrderStatusEnum::Shipped->canTransitionTo(OrderStatusEnum::Delivered));
    }

    public function test_shipped_transitions_to_cancelled(): void
    {
        $this->assertTrue(OrderStatusEnum::Shipped->canTransitionTo(OrderStatusEnum::Cancelled));
    }

    #[DataProvider('shippedInvalidTransitionProvider')]
    public function test_shipped_rejects_invalid_transitions(OrderStatusEnum $target): void
    {
        $this->assertFalse(OrderStatusEnum::Shipped->canTransitionTo($target));
    }

    #[DataProvider('terminalStatusProvider')]
    public function test_terminal_statuses_reject_all_non_self_transitions(OrderStatusEnum $status): void
    {
        foreach (OrderStatusEnum::cases() as $target) {
            if ($target === $status) {
                continue;
            }

            $this->assertFalse(
                $status->canTransitionTo($target),
                "Expected {$status->value} -> {$target->value} to be rejected",
            );
        }
    }

    public function test_same_status_transition_is_always_allowed(): void
    {
        foreach (OrderStatusEnum::cases() as $status) {
            $this->assertTrue(
                $status->canTransitionTo($status),
                "Expected {$status->value} -> {$status->value} to be allowed",
            );
        }
    }

    public function test_label_returns_string(): void
    {
        $this->assertSame('Awaiting payment', OrderStatusEnum::Pending->label());
        $this->assertSame('Paid', OrderStatusEnum::Paid->label());
        $this->assertSame('Shipped', OrderStatusEnum::Shipped->label());
        $this->assertSame('Delivered', OrderStatusEnum::Delivered->label());
        $this->assertSame('Cancelled', OrderStatusEnum::Cancelled->label());
    }

    public function test_color_returns_hex_string(): void
    {
        foreach (OrderStatusEnum::cases() as $status) {
            $this->assertMatchesRegularExpression('/^#[0-9a-fA-F]{6}$/', $status->color());
        }
    }

    public function test_user_message_returns_non_empty_string(): void
    {
        foreach (OrderStatusEnum::cases() as $status) {
            $this->assertNotEmpty($status->userMessage());
        }
    }

    public function test_should_notify_is_false_only_for_pending(): void
    {
        $this->assertFalse(OrderStatusEnum::Pending->shouldNotify());
        $this->assertTrue(OrderStatusEnum::Paid->shouldNotify());
        $this->assertTrue(OrderStatusEnum::Shipped->shouldNotify());
        $this->assertTrue(OrderStatusEnum::Delivered->shouldNotify());
        $this->assertTrue(OrderStatusEnum::Cancelled->shouldNotify());
    }

    /**
     * @return array<string, array{OrderStatusEnum}>
     */
    public static function pendingInvalidTransitionProvider(): array
    {
        return [
            'to shipped' => [OrderStatusEnum::Shipped],
            'to delivered' => [OrderStatusEnum::Delivered],
        ];
    }

    /**
     * @return array<string, array{OrderStatusEnum}>
     */
    public static function paidInvalidTransitionProvider(): array
    {
        return [
            'to pending' => [OrderStatusEnum::Pending],
            'to delivered' => [OrderStatusEnum::Delivered],
        ];
    }

    /**
     * @return array<string, array{OrderStatusEnum}>
     */
    public static function shippedInvalidTransitionProvider(): array
    {
        return [
            'to pending' => [OrderStatusEnum::Pending],
            'to paid' => [OrderStatusEnum::Paid],
        ];
    }

    /**
     * @return array<string, array{OrderStatusEnum}>
     */
    public static function terminalStatusProvider(): array
    {
        return [
            'delivered' => [OrderStatusEnum::Delivered],
            'cancelled' => [OrderStatusEnum::Cancelled],
        ];
    }
}
