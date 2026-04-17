<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class OrderItemApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Book $book;

    private Order $order;

    // -----------------------------------------------------------------------
    // GET /api/orderItems
    // -----------------------------------------------------------------------

    public function testGetListReturns200WithOrderItems(): void
    {
        OrderItem::factory()->count(3)->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->getJson('/api/orderItems');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'orderId', 'bookId', 'quantity', 'priceAtPurchase', 'createdAt', 'updatedAt'],
                ],
            ]);
    }

    public function testGetListReturnsEmptyDataWhenNoOrderItems(): void
    {
        $response = $this->getJson('/api/orderItems');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function testGetListRespectsPerPageParam(): void
    {
        OrderItem::factory()->count(10)->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->getJson('/api/orderItems?perPage=4');

        $response->assertStatus(200);
        self::assertCount(4, $response->json('data'));
    }

    public function testGetListSortsByQuantityDesc(): void
    {
        OrderItem::factory()->create(['quantity' => 1, 'order_id' => $this->order->id, 'book_id' => $this->book->id]);
        OrderItem::factory()->create(['quantity' => 9, 'order_id' => $this->order->id, 'book_id' => $this->book->id]);

        $response = $this->getJson('/api/orderItems?sortBy=quantity&sortDirection=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        self::assertSame(9, $data[0]['quantity']);
        self::assertSame(1, $data[1]['quantity']);
    }

    public function testGetListValidatesSortDirection(): void
    {
        $response = $this->getJson('/api/orderItems?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMin(): void
    {
        $response = $this->getJson('/api/orderItems?perPage=0');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMax(): void
    {
        $response = $this->getJson('/api/orderItems?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/orderItems/{orderItem}
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsOrderItem(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
            'quantity' => 3,
        ]);

        $response = $this->getJson("/api/orderItems/{$orderItem->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'orderId', 'bookId', 'quantity', 'priceAtPurchase', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.id', $orderItem->id)
            ->assertJsonPath('data.quantity', 3);
    }

    public function testGetByIdReturns404ForNonexistentOrderItem(): void
    {
        $response = $this->getJson('/api/orderItems/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/orderItems
    // -----------------------------------------------------------------------

    public function testCreateOrderItemReturns201WithData(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'orderId', 'bookId', 'quantity', 'priceAtPurchase', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.orderId', $this->order->id)
            ->assertJsonPath('data.bookId', $this->book->id);
    }

    public function testCreateOrderItemPersistsToDatabase(): void
    {
        $this->postJson('/api/orderItems', $this->validPayload(['quantity' => 5]));

        $this->assertDatabaseHas('order_items', [
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
            'quantity' => 5,
        ]);
    }

    public function testCreateOrderItemRequiresOrderId(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['orderId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orderId']);
    }

    public function testCreateOrderItemRequiresValidOrderId(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['orderId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orderId']);
    }

    public function testCreateOrderItemRequiresBookId(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['bookId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function testCreateOrderItemRequiresValidBookId(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['bookId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function testCreateOrderItemRequiresQuantity(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['quantity' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function testCreateOrderItemQuantityMustBeAtLeastOne(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['quantity' => 0]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function testCreateOrderItemRequiresPriceAtPurchase(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['priceAtPurchase' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priceAtPurchase']);
    }

    public function testCreateOrderItemPriceAtPurchaseCannotBeNegative(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['priceAtPurchase' => -1]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priceAtPurchase']);
    }

    public function testCreateOrderItemPriceAtPurchaseCannotExceedMax(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['priceAtPurchase' => 10000.00]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priceAtPurchase']);
    }

    // -----------------------------------------------------------------------
    // PUT /api/orderItems/{orderItem}
    // -----------------------------------------------------------------------

    public function testUpdateOrderItemReturns200WithUpdatedData(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/orderItems/{$orderItem->id}", $this->validPayload(['quantity' => 7]));

        $response->assertStatus(200)
            ->assertJsonPath('data.quantity', 7);
    }

    public function testUpdateOrderItemPersistsChangesToDatabase(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $this->putJson("/api/orderItems/{$orderItem->id}", $this->validPayload([
            'quantity' => 10,
            'priceAtPurchase' => 49.99,
        ]));

        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'quantity' => 10,
            'price_at_purchase' => 49.99,
        ]);
    }

    public function testUpdateOrderItemReturns404ForNonexistentOrderItem(): void
    {
        $response = $this->putJson('/api/orderItems/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function testUpdateOrderItemRequiresQuantity(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/orderItems/{$orderItem->id}", $this->validPayload(['quantity' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function testUpdateOrderItemQuantityMustBeAtLeastOne(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/orderItems/{$orderItem->id}", $this->validPayload(['quantity' => 0]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function testUpdateOrderItemRequiresValidOrderId(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/orderItems/{$orderItem->id}", $this->validPayload(['orderId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orderId']);
    }

    public function testUpdateOrderItemRequiresValidBookId(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/orderItems/{$orderItem->id}", $this->validPayload(['bookId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function testUpdateOrderItemPriceAtPurchaseCannotBeNegative(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/orderItems/{$orderItem->id}", $this->validPayload(['priceAtPurchase' => -1]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priceAtPurchase']);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/orderItems/{orderItem}
    // -----------------------------------------------------------------------

    public function testDeleteOrderItemReturns200WithMessage(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->deleteJson("/api/orderItems/{$orderItem->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'OrderItem deleted successfully']);
    }

    public function testDeleteOrderItemRemovesFromDatabase(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $this->deleteJson("/api/orderItems/{$orderItem->id}");

        $this->assertDatabaseMissing('order_items', ['id' => $orderItem->id]);
    }

    public function testDeleteOrderItemReturns404ForNonexistentOrderItem(): void
    {
        $response = $this->deleteJson('/api/orderItems/99999');

        $response->assertStatus(404);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create(['author_id' => Author::factory()->create()->id]);
        $this->order = Order::factory()->create(['user_id' => $this->user->id]);
    }

    /** @param array<string, mixed> $overrides
     *  @return array<string, mixed> */
    /** @param array<string, mixed> $overrides */
    /** @return array<string, mixed> */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'orderId' => $this->order->id,
            'bookId' => $this->book->id,
            'quantity' => 2,
            'priceAtPurchase' => 29.99,
        ], $overrides);
    }
}
