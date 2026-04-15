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

class OrderItemApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Book $book;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create(['author_id' => Author::factory()->create()->id]);
        $this->order = Order::factory()->create(['user_id' => $this->user->id]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'orderId' => $this->order->id,
            'bookId' => $this->book->id,
            'quantity' => 2,
            'priceAtPurchase' => 29.99,
        ], $overrides);
    }

    // -----------------------------------------------------------------------
    // GET /api/orderItems
    // -----------------------------------------------------------------------

    public function test_get_list_returns_200_with_order_items(): void
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

    public function test_get_list_returns_empty_data_when_no_order_items(): void
    {
        $response = $this->getJson('/api/orderItems');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function test_get_list_respects_per_page_param(): void
    {
        OrderItem::factory()->count(10)->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->getJson('/api/orderItems?perPage=4');

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data'));
    }

    public function test_get_list_sorts_by_quantity_desc(): void
    {
        OrderItem::factory()->create(['quantity' => 1, 'order_id' => $this->order->id, 'book_id' => $this->book->id]);
        OrderItem::factory()->create(['quantity' => 9, 'order_id' => $this->order->id, 'book_id' => $this->book->id]);

        $response = $this->getJson('/api/orderItems?sortBy=quantity&sortDirection=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertSame(9, $data[0]['quantity']);
        $this->assertSame(1, $data[1]['quantity']);
    }

    public function test_get_list_validates_sort_direction(): void
    {
        $response = $this->getJson('/api/orderItems?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_min(): void
    {
        $response = $this->getJson('/api/orderItems?perPage=0');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_max(): void
    {
        $response = $this->getJson('/api/orderItems?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/orderItems/{orderItem}
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_order_item(): void
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

    public function test_get_by_id_returns_404_for_nonexistent_order_item(): void
    {
        $response = $this->getJson('/api/orderItems/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/orderItems
    // -----------------------------------------------------------------------

    public function test_create_order_item_returns_201_with_data(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'orderId', 'bookId', 'quantity', 'priceAtPurchase', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.orderId', $this->order->id)
            ->assertJsonPath('data.bookId', $this->book->id);
    }

    public function test_create_order_item_persists_to_database(): void
    {
        $this->postJson('/api/orderItems', $this->validPayload(['quantity' => 5]));

        $this->assertDatabaseHas('order_items', [
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
            'quantity' => 5,
        ]);
    }

    public function test_create_order_item_requires_order_id(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['orderId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orderId']);
    }

    public function test_create_order_item_requires_valid_order_id(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['orderId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orderId']);
    }

    public function test_create_order_item_requires_book_id(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['bookId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function test_create_order_item_requires_valid_book_id(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['bookId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function test_create_order_item_requires_quantity(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['quantity' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_create_order_item_quantity_must_be_at_least_one(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['quantity' => 0]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_create_order_item_requires_price_at_purchase(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['priceAtPurchase' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priceAtPurchase']);
    }

    public function test_create_order_item_price_at_purchase_cannot_be_negative(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['priceAtPurchase' => -1]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priceAtPurchase']);
    }

    public function test_create_order_item_price_at_purchase_cannot_exceed_max(): void
    {
        $response = $this->postJson('/api/orderItems', $this->validPayload(['priceAtPurchase' => 10000.00]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priceAtPurchase']);
    }

    // -----------------------------------------------------------------------
    // PUT /api/orderItems/{orderItem}
    // -----------------------------------------------------------------------

    public function test_update_order_item_returns_200_with_updated_data(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/orderItems/{$orderItem->id}", $this->validPayload(['quantity' => 7]));

        $response->assertStatus(200)
            ->assertJsonPath('data.quantity', 7);
    }

    public function test_update_order_item_persists_changes_to_database(): void
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

    public function test_update_order_item_returns_404_for_nonexistent_order_item(): void
    {
        $response = $this->putJson('/api/orderItems/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function test_update_order_item_requires_quantity(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/orderItems/{$orderItem->id}", $this->validPayload(['quantity' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_update_order_item_quantity_must_be_at_least_one(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/orderItems/{$orderItem->id}", $this->validPayload(['quantity' => 0]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_update_order_item_requires_valid_order_id(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/orderItems/{$orderItem->id}", $this->validPayload(['orderId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['orderId']);
    }

    public function test_update_order_item_requires_valid_book_id(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/orderItems/{$orderItem->id}", $this->validPayload(['bookId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function test_update_order_item_price_at_purchase_cannot_be_negative(): void
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

    public function test_delete_order_item_returns_200_with_message(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->deleteJson("/api/orderItems/{$orderItem->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'OrderItem deleted successfully']);
    }

    public function test_delete_order_item_removes_from_database(): void
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'book_id' => $this->book->id,
        ]);

        $this->deleteJson("/api/orderItems/{$orderItem->id}");

        $this->assertDatabaseMissing('order_items', ['id' => $orderItem->id]);
    }

    public function test_delete_order_item_returns_404_for_nonexistent_order_item(): void
    {
        $response = $this->deleteJson('/api/orderItems/99999');

        $response->assertStatus(404);
    }
}
