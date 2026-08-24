<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Order;

use App\Dto\Order\OrderDto;
use App\Listeners\SendOrderCreatedEmailListener;
use App\Models\Author;
use App\Models\Book;
use App\Models\CartItem;
use App\Models\User;
use App\Services\Order\CreateOrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class OrderEmailAfterCommitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
        config(['queue.default' => 'database']);
    }

    public function test_rolled_back_checkout_does_not_queue_order_created_email(): void
    {
        $user = User::factory()->create();
        $firstBook = Book::factory()->create([
            'author_id' => Author::factory()->create()->id,
            'status' => 'active',
            'stock' => 10,
            'price' => '50.00',
        ]);
        $secondBook = Book::factory()->create([
            'author_id' => Author::factory()->create()->id,
            'status' => 'active',
            'stock' => 5,
            'price' => '30.00',
        ]);

        CartItem::factory()->create([
            'user_id' => $user->id,
            'book_id' => $firstBook->id,
            'quantity' => 1,
        ]);
        CartItem::factory()->create([
            'user_id' => $user->id,
            'book_id' => $secondBook->id,
            'quantity' => 2,
        ]);

        $secondBook->update(['stock' => 1]);

        try {
            $this->app->make(CreateOrderService::class)->execute($this->makeDto($user->id));
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException) {
        }

        $this->assertSame(0, $this->notificationJobsCount());
        $this->assertDatabaseMissing('orders', ['user_id' => $user->id]);
        $this->assertSame(1, $secondBook->fresh()->stock);
        $this->assertSame(10, $firstBook->fresh()->stock);
        $this->assertSame(2, (int) DB::table('cart_items')->where('user_id', $user->id)->count());
    }

    public function test_successful_checkout_queues_order_created_email_after_commit(): void
    {
        $user = User::factory()->create();
        $firstBook = Book::factory()->create([
            'author_id' => Author::factory()->create()->id,
            'status' => 'active',
            'stock' => 10,
            'price' => '50.00',
        ]);
        $secondBook = Book::factory()->create([
            'author_id' => Author::factory()->create()->id,
            'status' => 'active',
            'stock' => 5,
            'price' => '30.00',
        ]);

        CartItem::factory()->create([
            'user_id' => $user->id,
            'book_id' => $firstBook->id,
            'quantity' => 1,
        ]);
        CartItem::factory()->create([
            'user_id' => $user->id,
            'book_id' => $secondBook->id,
            'quantity' => 2,
        ]);

        $this->app->make(CreateOrderService::class)->execute($this->makeDto($user->id));

        $notificationJobs = $this->notificationJobs();
        $this->assertCount(1, $notificationJobs);

        $job = $notificationJobs[0];
        $this->assertSame('notifications', $job->queue);
        $this->assertStringContainsString(
            addcslashes(SendOrderCreatedEmailListener::class, '\\'),
            $job->payload,
        );

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => '110.00',
        ]);
        $this->assertSame(9, $firstBook->fresh()->stock);
        $this->assertSame(3, $secondBook->fresh()->stock);
    }

    private function makeDto(int $userId): OrderDto
    {
        return new OrderDto(
            userId: $userId,
            status: 'pending',
            shippingAddress: 'Test address 1',
            paymentMethod: 'card',
        );
    }

    /**
     * @return array<int, object{payload: string, queue: string}>
     */
    private function notificationJobs(): array
    {
        $needle = addcslashes(SendOrderCreatedEmailListener::class, '\\');

        return DB::table('jobs')
            ->where('queue', 'notifications')
            ->get()
            ->filter(static fn (object $job): bool => str_contains($job->payload, $needle))
            ->values()
            ->all();
    }

    private function notificationJobsCount(): int
    {
        return count($this->notificationJobs());
    }
}
