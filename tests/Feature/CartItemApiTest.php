<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CartItemApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Book $book;

    private Author $author;

    // -----------------------------------------------------------------------
    // GET /api/cartItems
    // -----------------------------------------------------------------------

    public function testGetListReturns200WithCartItems(): void
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

    public function testGetListReturnsEmptyDataWhenNoCartItems(): void
    {
        $response = $this->getJson('/api/cartItems');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function testGetListRespectsPerPageParam(): void
    {
        CartItem::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->getJson('/api/cartItems?perPage=3');

        $response->assertStatus(200);
        self::assertCount(3, $response->json('data'));
    }

    public function testGetListValidatesSortDirection(): void
    {
        $response = $this->getJson('/api/cartItems?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMin(): void
    {
        $response = $this->getJson('/api/cartItems?perPage=0');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMax(): void
    {
        $response = $this->getJson('/api/cartItems?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/cartItems/{cartItem}
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsCartItem(): void
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

    public function testGetByIdReturns404ForNonexistentCartItem(): void
    {
        $response = $this->getJson('/api/cartItems/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/cartItems
    // -----------------------------------------------------------------------

    public function testCreateCartItemReturns201WithData(): void
    {
        $response = $this->postJson('/api/cartItems', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'userId', 'bookId', 'quantity', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.quantity', 2);
    }

    public function testCreateCartItemPersistsToDatabase(): void
    {
        $this->postJson('/api/cartItems', $this->validPayload(['quantity' => 5]));

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'quantity' => 5,
        ]);
    }

    public function testCreateCartItemRequiresUserId(): void
    {
        $response = $this->postJson('/api/cartItems', $this->validPayload(['userId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function testCreateCartItemRequiresValidUserId(): void
    {
        $response = $this->postJson('/api/cartItems', $this->validPayload(['userId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function testCreateCartItemRequiresValidBookId(): void
    {
        $response = $this->postJson('/api/cartItems', $this->validPayload(['bookId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bookId']);
    }

    public function testCreateCartItemRequiresQuantity(): void
    {
        $response = $this->postJson('/api/cartItems', $this->validPayload(['quantity' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    // -----------------------------------------------------------------------
    // PUT /api/cartItems/{cartItem}
    // -----------------------------------------------------------------------

    public function testUpdateCartItemReturns200WithUpdatedData(): void
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

    public function testUpdateCartItemPersistsChangesToDatabase(): void
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

    public function testUpdateCartItemReturns404ForNonexistentCartItem(): void
    {
        $response = $this->putJson('/api/cartItems/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function testUpdateCartItemRequiresQuantity(): void
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

    public function testDeleteCartItemReturns200WithMessage(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->deleteJson("/api/cartItems/{$cartItem->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'CartItem deleted successfully']);
    }

    public function testDeleteCartItemRemovesFromDatabase(): void
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $this->deleteJson("/api/cartItems/{$cartItem->id}");

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function testDeleteCartItemReturns404ForNonexistentCartItem(): void
    {
        $response = $this->deleteJson('/api/cartItems/99999');

        $response->assertStatus(404);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->author = Author::factory()->create();
        $this->book = Book::factory()->create(['author_id' => $this->author->id]);
    }

    /** @param array<string, mixed> $overrides
     *  @return array<string, mixed> */
    /** @param array<string, mixed> $overrides */
    /** @return array<string, mixed> */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'userId' => $this->user->id,
            'bookId' => $this->book->id,
            'quantity' => 2,
        ], $overrides);
    }
}
