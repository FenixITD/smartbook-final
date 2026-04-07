<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartItemApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Book $book;

    private Author $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->author = Author::factory()->create();
        $this->book = Book::factory()->create(['author_id' => $this->author->id]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'userId' => $this->user->id,
            'bookId' => $this->book->id,
            'quantity' => 2,
        ], $overrides);
    }

    // -----------------------------------------------------------------------
    // GET /api/cartItems
    // -----------------------------------------------------------------------

    public function test_get_list_returns_200_with_cart_items(): void
    {
        CartItem::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->getJson('/api/cartItems');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'userId', 'bookId', 'quantity', 'createdAt', 'updatedAt'],
                ],
            ]);
    }

    public function test_get_list_returns_empty_data_when_no_cart_items(): void
    {
        $response = $this->getJson('/api/cartItems');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function test_get_list_respects_per_page_param(): void
    {
        CartItem::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->getJson('/api/cartItems?perPage=3');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_get_list_validates_sort_direction(): void
    {
        $response = $this->getJson('/api/cartItems?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_min(): void
    {
        $response = $this->getJson('/api/cartItems?perPage=0');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_max(): void
    {
        $response = $this->getJson('/api/cartItems?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/cartItems/{cartItem}
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_cart_item(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 3,
        ]);

        $response = $this->getJson("/api/cartItems/{$cartItem->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'userId', 'bookId', 'quantity', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.id', $cartItem->id)
            ->assertJsonPath('data.quantity', 3);
    }

    public function test_get_by_id_returns_404_for_nonexistent_cart_item(): void
    {
        $response = $this->getJson('/api/cartItems/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/cartItems
    // -----------------------------------------------------------------------

    public function test_create_cart_item_returns_201_with_data(): void
    {
        $response = $this->postJson('/api/cartItems', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'userId', 'bookId', 'quantity', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.quantity', 2);
    }

    public function test_create_cart_item_persists_to_database(): void
    {
        $this->postJson('/api/cartItems', $this->validPayload(['quantity' => 5]));

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 5,
        ]);
    }

    public function test_create_cart_item_requires_user_id(): void
    {
        $response = $this->postJson('/api/cartItems', $this->validPayload(['userId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function test_create_cart_item_requires_valid_user_id(): void
    {
        $response = $this->postJson('/api/cartItems', $this->validPayload(['userId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function test_create_cart_item_requires_valid_book_id(): void
    {
        $response = $this->postJson('/api/cartItems', $this->validPayload(['bookId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function test_create_cart_item_requires_quantity(): void
    {
        $response = $this->postJson('/api/cartItems', $this->validPayload(['quantity' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    // -----------------------------------------------------------------------
    // PUT /api/cartItems/{cartItem}
    // -----------------------------------------------------------------------

    public function test_update_cart_item_returns_200_with_updated_data(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 1,
        ]);

        $response = $this->putJson("/api/cartItems/{$cartItem->id}", $this->validPayload(['quantity' => 10]));

        $response->assertStatus(200)
            ->assertJsonPath('data.quantity', 10);
    }

    public function test_update_cart_item_persists_changes_to_database(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 1,
        ]);

        $this->putJson("/api/cartItems/{$cartItem->id}", $this->validPayload(['quantity' => 7]));

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 7,
        ]);
    }

    public function test_update_cart_item_returns_404_for_nonexistent_cart_item(): void
    {
        $response = $this->putJson('/api/cartItems/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function test_update_cart_item_requires_quantity(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->putJson("/api/cartItems/{$cartItem->id}", $this->validPayload(['quantity' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/cartItems/{cartItem}
    // -----------------------------------------------------------------------

    public function test_delete_cart_item_returns_200_with_message(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->deleteJson("/api/cartItems/{$cartItem->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'CartItem deleted successfully']);
    }

    public function test_delete_cart_item_removes_from_database(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $this->deleteJson("/api/cartItems/{$cartItem->id}");

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_delete_cart_item_returns_404_for_nonexistent_cart_item(): void
    {
        $response = $this->deleteJson('/api/cartItems/99999');

        $response->assertStatus(404);
    }
}
