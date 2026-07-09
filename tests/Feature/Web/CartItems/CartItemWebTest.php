<?php declare(strict_types=1);

namespace Tests\Feature\Web\CartItems;

use App\Http\Controllers\Web\CartItems\ClearCartController;
use App\Models\Author;
use App\Models\Book;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class CartItemWebTest extends TestCase
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

        Route::post('/test-clear-cart', ClearCartController::class);
    }

    public function test_guest_can_view_cart(): void
    {
        $response = $this->get('/cart');
        $response->assertStatus(200)->assertViewIs('cart.index');
    }

    public function test_guest_can_add_to_cart(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 10]);

        $response = $this->post('/cart', [
            'book_id' => $book->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(2, session('guest_cart')[$book->id]['quantity']);
    }

    public function test_guest_cannot_add_more_than_stock(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 5]);

        $response = $this->post('/cart', [
            'book_id' => $book->id,
            'quantity' => 10,
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertNull(session('guest_cart'));
    }

    public function test_guest_can_update_cart(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 10]);
        session(['guest_cart' => [$book->id => ['book_id' => $book->id, 'quantity' => 1]]]);

        $response = $this->put("/cart/{$book->id}", [
            'quantity' => 5,
        ]);

        $response->assertRedirect();
        $this->assertEquals(5, session('guest_cart')[$book->id]['quantity']);
    }

    public function test_guest_can_remove_from_cart(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        session(['guest_cart' => [$book->id => ['book_id' => $book->id, 'quantity' => 1]]]);

        $response = $this->delete("/cart/{$book->id}");

        $response->assertRedirect();
        $this->assertArrayNotHasKey($book->id, session('guest_cart'));
    }

    public function test_guest_can_clear_cart(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        session(['guest_cart' => [$book->id => ['book_id' => $book->id, 'quantity' => 1]]]);

        $response = $this->post('/test-clear-cart');

        $response->assertRedirect();
        $this->assertNull(session('guest_cart'));
    }

    public function test_auth_user_can_view_cart(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/cart');
        $response->assertStatus(200)->assertViewIs('cart.index');
    }

    public function test_auth_user_can_add_to_cart(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 10]);

        $response = $this->actingAs($user)->post('/cart', [
            'book_id' => $book->id,
            'quantity' => 3,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'quantity' => 3,
        ]);
    }

    public function test_auth_user_can_update_cart(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 10]);
        CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 1]);

        $response = $this->actingAs($user)->put("/cart/{$book->id}", [
            'quantity' => 8,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'quantity' => 8,
        ]);
    }

    public function test_auth_user_can_remove_from_cart(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);
        $cartItem = CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)->delete("/cart/{$book->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_auth_user_can_clear_cart(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $books = Book::factory()->count(2)->create(['author_id' => $author->id]);
        foreach ($books as $b) {
            CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $b->id]);
        }

        $response = $this->actingAs($user)->post('/test-clear-cart');

        $response->assertRedirect();
        $this->assertDatabaseMissing('cart_items', ['user_id' => $user->id]);
    }

    public function test_guest_cart_merges_on_login(): void
    {
        $user = User::factory()->create(['email' => 'merge@example.com']);
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'stock' => 15]);

        session(['guest_cart' => [$book->id => ['book_id' => $book->id, 'quantity' => 3]]]);

        $this->post('/login', [
            'email' => 'merge@example.com',
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'quantity' => 3,
        ]);
        $this->assertNull(session('guest_cart'));
    }
}
