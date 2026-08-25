<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Order;

use App\Dto\Order\OrderDto;
use App\Dto\OrderItem\OrderItemInputDto;
use App\Models\Author;
use App\Models\Book;
use App\Models\CartItem;
use App\Models\User;
use App\Services\Order\CreateOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class CreateOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private CreateOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(CreateOrderService::class);
    }

    public function test_creates_order_with_direct_items(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'price' => 30.00, 'stock' => 10, 'status' => 'active']);

        $dto = new OrderDto(
            userId: $user->id,
            status: 'pending',
            shippingAddress: '123 Main St',
            paymentMethod: 'card',
            items: [new OrderItemInputDto(bookId: $book->id, quantity: 2)],
        );

        $order = $this->service->execute($dto);

        $this->assertSame('pending', $order->status);
        $this->assertSame('60.00', $order->total);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total' => '60.00']);
        $this->assertDatabaseHas('order_items', ['book_id' => $book->id, 'quantity' => 2, 'price_at_purchase' => '30.00']);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 8]);
    }

    public function test_creates_order_from_cart_when_items_null(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'price' => 15.00, 'stock' => 10, 'status' => 'active']);

        CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 3]);

        $dto = new OrderDto(
            userId: $user->id,
            status: 'pending',
            shippingAddress: '456 Elm St',
            paymentMethod: 'webpay',
        );

        $order = $this->service->execute($dto);

        $this->assertSame('45.00', $order->total);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 7]);
        $this->assertDatabaseMissing('cart_items', ['user_id' => $user->id]);
    }

    public function test_throws_when_cart_is_empty(): void
    {
        $user = User::factory()->create();

        $dto = new OrderDto(
            userId: $user->id,
            status: 'pending',
            shippingAddress: '123 Main St',
            paymentMethod: 'card',
        );

        $this->expectException(ValidationException::class);
        $this->service->execute($dto);
    }

    public function test_throws_when_book_not_available(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'price' => 10.00, 'stock' => 5, 'status' => 'inactive']);

        CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 1]);

        $dto = new OrderDto(
            userId: $user->id,
            status: 'pending',
            shippingAddress: '123 Main St',
            paymentMethod: 'card',
        );

        $this->expectException(ValidationException::class);
        $this->service->execute($dto);
    }

    public function test_throws_when_insufficient_stock(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'price' => 10.00, 'stock' => 2, 'status' => 'active']);

        CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 5]);

        $dto = new OrderDto(
            userId: $user->id,
            status: 'pending',
            shippingAddress: '123 Main St',
            paymentMethod: 'card',
        );

        $this->expectException(ValidationException::class);
        $this->service->execute($dto);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 2]);
    }

    public function test_merges_duplicate_book_ids(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'price' => 10.00, 'stock' => 10, 'status' => 'active']);

        $dto = new OrderDto(
            userId: $user->id,
            status: 'pending',
            shippingAddress: '123 Main St',
            paymentMethod: 'card',
            items: [
                new OrderItemInputDto(bookId: $book->id, quantity: 2),
                new OrderItemInputDto(bookId: $book->id, quantity: 3),
            ],
        );

        $order = $this->service->execute($dto);

        $this->assertSame('50.00', $order->total);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 5]);
    }

    public function test_clears_cart_after_order_from_cart(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book1 = Book::factory()->create(['author_id' => $author->id, 'price' => 10.00, 'stock' => 10, 'status' => 'active']);
        $book2 = Book::factory()->create(['author_id' => $author->id, 'price' => 20.00, 'stock' => 10, 'status' => 'active']);

        CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'quantity' => 1]);
        CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'quantity' => 2]);

        $dto = new OrderDto(
            userId: $user->id,
            status: 'pending',
            shippingAddress: '123 Main St',
            paymentMethod: 'card',
        );

        $this->service->execute($dto);

        $this->assertDatabaseMissing('cart_items', ['user_id' => $user->id]);
    }

    public function test_stock_decremented_by_correct_quantity(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'price' => 5.00, 'stock' => 20, 'status' => 'active']);

        $dto = new OrderDto(
            userId: $user->id,
            status: 'pending',
            shippingAddress: '123 Main St',
            paymentMethod: 'card',
            items: [new OrderItemInputDto(bookId: $book->id, quantity: 7)],
        );

        $this->service->execute($dto);

        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 13]);
    }
}
