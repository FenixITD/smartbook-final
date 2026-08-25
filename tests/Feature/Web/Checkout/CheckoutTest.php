<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Checkout;

use App\Models\Author;
use App\Models\Book;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CheckoutTest extends TestCase
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

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('checkout.create'));

        $response->assertRedirect('/login');
    }

    public function test_user_can_view_checkout_page(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'price' => 25.00, 'stock' => 5]);

        CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 2]);

        $response = $this->actingAs($user)->get(route('checkout.create'));

        $response->assertOk()->assertViewIs('orders.checkout');
    }

    public function test_user_can_place_order_from_cart(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'price' => 20.00, 'stock' => 10, 'status' => 'active']);

        CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 3]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'shippingAddress' => '123 Main St',
            'paymentMethod' => 'card',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
            'shipping_address' => '123 Main St',
            'payment_method' => 'card',
            'total' => '60.00',
        ]);
        $this->assertDatabaseHas('order_items', [
            'book_id' => $book->id,
            'quantity' => 3,
            'price_at_purchase' => '20.00',
        ]);
        $this->assertDatabaseMissing('cart_items', ['user_id' => $user->id]);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 7]);
    }

    public function test_order_total_calculated_from_cart(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book1 = Book::factory()->create(['author_id' => $author->id, 'price' => 15.50, 'stock' => 10, 'status' => 'active']);
        $book2 = Book::factory()->create(['author_id' => $author->id, 'price' => 24.75, 'stock' => 10, 'status' => 'active']);

        CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'quantity' => 2]);
        CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'quantity' => 1]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'shippingAddress' => '456 Elm St',
            'paymentMethod' => 'webpay',
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => '55.75',
        ]);
    }

    public function test_user_id_taken_from_auth_not_from_request(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'price' => 10.00, 'stock' => 5, 'status' => 'active']);

        CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 1]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'shippingAddress' => '789 Oak St',
            'paymentMethod' => 'cash',
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseMissing('orders', [
            'user_id' => $otherUser->id,
        ]);
    }

    public function test_checkout_fails_when_cart_is_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'shippingAddress' => '123 Main St',
            'paymentMethod' => 'card',
        ]);

        $response->assertUnprocessable();
    }

    public function test_checkout_fails_when_insufficient_stock(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'price' => 10.00, 'stock' => 1, 'status' => 'active']);

        CartItem::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'quantity' => 5]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'shippingAddress' => '123 Main St',
            'paymentMethod' => 'card',
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseMissing('orders', ['user_id' => $user->id]);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 1]);
    }

    public function test_checkout_validates_payment_method(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'shippingAddress' => '123 Main St',
            'paymentMethod' => 'bitcoin',
        ]);

        $response->assertUnprocessable();
    }

    public function test_checkout_validates_shipping_address(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'paymentMethod' => 'card',
        ]);

        $response->assertUnprocessable();
    }
}
