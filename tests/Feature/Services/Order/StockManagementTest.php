<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Order;

use App\Models\Author;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Services\Order\DeleteOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_decrement_stock_reduces_quantity(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 5, 'status' => 'active']);

        $repo = $this->app->make(BookRepositoryInterface::class);
        $result = $repo->decrementStock($book->id, 2);

        $this->assertTrue($result);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 3]);
    }

    public function test_decrement_stock_returns_false_when_insufficient(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 1, 'status' => 'active']);

        $repo = $this->app->make(BookRepositoryInterface::class);
        $result = $repo->decrementStock($book->id, 5);

        $this->assertFalse($result);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 1]);
    }

    public function test_increment_stock_increases_quantity(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 3, 'status' => 'active']);

        $repo = $this->app->make(BookRepositoryInterface::class);
        $result = $repo->incrementStock($book->id, 4);

        $this->assertTrue($result);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 7]);
    }

    public function test_delete_pending_order_restores_stock(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 0, 'status' => 'active']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $book->id, 'quantity' => 10]);

        $this->app->make(DeleteOrderService::class)->execute($order->id);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 10]);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_delete_paid_order_restores_stock(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 0, 'status' => 'active']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $book->id, 'quantity' => 5]);

        $this->app->make(DeleteOrderService::class)->execute($order->id);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 5]);
    }

    public function test_delete_shipped_order_restores_stock(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 0, 'status' => 'active']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'shipped']);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $book->id, 'quantity' => 3]);

        $this->app->make(DeleteOrderService::class)->execute($order->id);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 3]);
    }

    public function test_delete_delivered_order_does_not_restore_stock(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 10, 'status' => 'active']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'delivered']);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $book->id, 'quantity' => 4]);

        $this->app->make(DeleteOrderService::class)->execute($order->id);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 10]);
    }

    public function test_delete_cancelled_order_does_not_restore_stock(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 10, 'status' => 'active']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'cancelled']);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $book->id, 'quantity' => 4]);

        $this->app->make(DeleteOrderService::class)->execute($order->id);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 10]);
    }
}
