<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Orders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderStockRestorationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (class_exists(ValidateCsrfToken::class)) {
            $this->withoutMiddleware(ValidateCsrfToken::class);
        }
        if (class_exists(VerifyCsrfToken::class)) {
            $this->withoutMiddleware(VerifyCsrfToken::class);
        }
    }

    private function makeBook(int $stock): Book
    {
        return Book::factory()->for(Author::factory())->create([
            'status' => 'active',
            'stock' => $stock,
        ]);
    }

    public function test_cancelling_pending_order_restores_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $firstBook = $this->makeBook(stock: 10);
        $secondBook = $this->makeBook(stock: 7);

        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $firstBook->id, 'quantity' => 4]);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $secondBook->id, 'quantity' => 2]);

        $this->actingAs($admin)->put("/orders/{$order->id}", [
            'userId' => $user->id,
            'status' => 'cancelled',
            'shippingAddress' => '789 Oak St',
            'paymentMethod' => 'card',
        ]);

        $this->assertDatabaseHas('books', ['id' => $firstBook->id, 'stock' => 14]);
        $this->assertDatabaseHas('books', ['id' => $secondBook->id, 'stock' => 9]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }

    public function test_repeated_cancellation_does_not_double_restore_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $book = $this->makeBook(stock: 10);

        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $book->id, 'quantity' => 4]);

        $this->actingAs($admin)->put("/orders/{$order->id}", [
            'userId' => $user->id,
            'status' => 'cancelled',
            'shippingAddress' => '789 Oak St',
            'paymentMethod' => 'card',
        ]);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 14]);

        $this->actingAs($admin)->put("/orders/{$order->id}", [
            'userId' => $user->id,
            'status' => 'cancelled',
            'shippingAddress' => '789 Oak St',
            'paymentMethod' => 'card',
        ]);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 14]);
    }

    public function test_deleting_pending_order_restores_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $book = $this->makeBook(stock: 0);

        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $book->id, 'quantity' => 10]);

        $response = $this->actingAs($admin)->delete("/orders/{$order->id}");

        $response->assertRedirect(route('orders.index'));
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_items', ['order_id' => $order->id]);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 10]);
    }

    public function test_deleting_paid_and_shipped_orders_restores_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $paidBook = $this->makeBook(stock: 0);
        $shippedBook = $this->makeBook(stock: 3);

        $paidOrder = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->create(['order_id' => $paidOrder->id, 'book_id' => $paidBook->id, 'quantity' => 6]);

        $shippedOrder = Order::factory()->create(['user_id' => $user->id, 'status' => 'shipped']);
        OrderItem::factory()->create(['order_id' => $shippedOrder->id, 'book_id' => $shippedBook->id, 'quantity' => 5]);

        $this->actingAs($admin)->delete("/orders/{$paidOrder->id}");
        $this->actingAs($admin)->delete("/orders/{$shippedOrder->id}");

        $this->assertDatabaseHas('books', ['id' => $paidBook->id, 'stock' => 6]);
        $this->assertDatabaseHas('books', ['id' => $shippedBook->id, 'stock' => 8]);
    }

    public function test_deleting_cancelled_order_does_not_change_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $book = $this->makeBook(stock: 10);

        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'cancelled']);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $book->id, 'quantity' => 4]);

        $this->actingAs($admin)->delete("/orders/{$order->id}");

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 10]);
    }

    public function test_deleting_delivered_order_does_not_change_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $book = $this->makeBook(stock: 10);

        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'delivered']);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $book->id, 'quantity' => 4]);

        $this->actingAs($admin)->delete("/orders/{$order->id}");

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 10]);
    }

    public function test_status_transitions_to_paid_and_shipped_keep_stock_intact(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $book = $this->makeBook(stock: 6);

        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $book->id, 'quantity' => 4]);

        foreach (['paid', 'shipped'] as $status) {
            $this->actingAs($admin)->put("/orders/{$order->id}", [
                'userId' => $user->id,
                'status' => $status,
                'shippingAddress' => '789 Oak St',
                'paymentMethod' => 'card',
            ]);
        }

        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 6]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'shipped']);
    }
}
