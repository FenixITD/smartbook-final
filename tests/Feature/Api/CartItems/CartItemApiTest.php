<?php declare(strict_types=1);

namespace Tests\Feature\Api\CartItems;

use App\Models\Author;
use App\Models\Book;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CartItemApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_cart_items_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $author = Author::factory()->create();

        $books = Book::factory()->count(3)->create(['author_id' => $author->id]);
        foreach ($books as $b) {
            CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $b->id]);
        }

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/cartItems');

        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_admin_can_get_single_cart_item(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $cartItem = CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($admin, 'sanctum')->getJson("/api/cartItems/{$cartItem->id}");

        $response->assertStatus(200)->assertJsonPath('data.id', $cartItem->id);
    }

    public function test_admin_can_create_cart_item(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'status' => 'active']);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/cartItems', [
            'userId' => $user->id,
            'bookId' => $book->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.quantity', 2);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'quantity' => 2,
        ]);
    }

    public function test_admin_can_update_cart_item(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'status' => 'active']);
        $cartItem = CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 1]);

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/cartItems/{$cartItem->id}", [
            'userId' => $user->id,
            'bookId' => $book->id,
            'quantity' => 5,
        ]);

        $response->assertStatus(200)->assertJsonPath('data.quantity', 5);
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    public function test_admin_can_delete_cart_item(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $cartItem = CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/cartItems/{$cartItem->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_api_cart_item_validation_fails(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/cartItems', [
            'userId' => 999,
            'bookId' => 999,
            'quantity' => 'invalid',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['userId', 'bookId', 'quantity']);
    }
}
