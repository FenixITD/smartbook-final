<?php

declare(strict_types=1);

namespace Tests\Feature\Listeners;

use App\Dto\Order\OrderStatusChangedDto;
use App\Events\OrderCreatedEvent;
use App\Events\OrderStatusChangedEvent;
use App\Listeners\SendOrderCreatedEmailListener;
use App\Listeners\SendOrderStatusEmailListener;
use App\Mail\OrderStatusMail;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class OrderNotificationEmailTest extends TestCase
{
    private function makeDto(int $orderId, string $oldStatus = '', string $newStatus = 'pending'): OrderStatusChangedDto
    {
        return new OrderStatusChangedDto(
            orderId: $orderId,
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            userEmail: 'user@example.com',
            userName: 'User',
            total: '100.00',
        );
    }

    public function test_order_email_listeners_do_not_use_global_uniqueness(): void
    {
        $this->assertFalse(
            (new SendOrderCreatedEmailListener) instanceof ShouldBeUnique
        );
        $this->assertFalse(
            (new SendOrderStatusEmailListener) instanceof ShouldBeUnique
        );
    }

    public function test_order_email_listeners_are_registered_exactly_once(): void
    {
        $this->assertCount(1, Event::getListeners(OrderCreatedEvent::class));
        $this->assertCount(1, Event::getListeners(OrderStatusChangedEvent::class));
    }

    public function test_order_email_listeners_dispatch_after_commit(): void
    {
        $this->assertTrue((new SendOrderCreatedEmailListener)->afterCommit);
        $this->assertTrue((new SendOrderStatusEmailListener)->afterCommit);
    }

    public function test_created_emails_for_different_orders_are_all_queued(): void
    {
        Queue::fake();

        Event::dispatch(new OrderCreatedEvent($this->makeDto(orderId: 1)));
        Event::dispatch(new OrderCreatedEvent($this->makeDto(orderId: 2)));

        Queue::assertPushed(CallQueuedListener::class, 2);
    }

    public function test_consecutive_status_emails_for_same_order_are_all_queued(): void
    {
        Queue::fake();

        Event::dispatch(new OrderStatusChangedEvent(
            $this->makeDto(orderId: 7, oldStatus: 'pending', newStatus: 'paid')
        ));
        Event::dispatch(new OrderStatusChangedEvent(
            $this->makeDto(orderId: 7, oldStatus: 'paid', newStatus: 'shipped')
        ));

        Queue::assertPushed(CallQueuedListener::class, 2);
    }

    public function test_notification_events_reach_mail_layer(): void
    {
        Mail::fake();

        Event::dispatch(new OrderCreatedEvent($this->makeDto(orderId: 3)));
        Event::dispatch(new OrderStatusChangedEvent(
            $this->makeDto(orderId: 3, oldStatus: '', newStatus: 'pending')
        ));

        Mail::assertSent(OrderStatusMail::class, 2);
    }
}
