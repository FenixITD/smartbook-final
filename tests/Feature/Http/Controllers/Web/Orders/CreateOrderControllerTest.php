<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Web\Orders;

use App\Models\{Author, Book, CartItem, User};
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_store_calculates_total_server_side_and_creates_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        $author = Author::factory()->create();

        $book1 = Book::factory()->create(['author_id' => $author->id, 'price' => 20.00, 'stock' => 10, 'status' => 'active']);
        $book2 = Book::factory()->create(['author_id' => $author->id, 'price' => 30.00, 'stock' => 10, 'status' => 'active']);

        CartItem::factory()->create(['user_id' => $customer->id, 'book_id' => $book1->id, 'quantity' => 2]);
        CartItem::factory()->create(['user_id' => $customer->id, 'book_id' => $book2->id, 'quantity' => 1]);

        $response = $this->actingAs($admin)->post(route('orders.store'), [
            'userId' => $customer->id,
            'status' => 'pending',
            'shippingAddress' => '123 Main St',
            'paymentMethod' => 'card',
        ]);

        $response->assertRedirect(route('orders.index'));
        $this->assertDatabaseHas('orders', ['user_id' => $customer->id, 'total' => 70.00]);
        $this->assertDatabaseMissing('cart_items', ['user_id' => $customer->id]);
    }

    public function test_non_admin_cannot_create_order(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post(route('orders.store'), [
            'userId' => $user->id,
            'status' => 'pending',
            'shippingAddress' => '123 Main St',
            'paymentMethod' => 'card',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_create_order(): void
    {
        $response = $this->post(route('orders.store'), [
            'userId' => 1,
            'status' => 'pending',
            'shippingAddress' => '123 Main St',
            'paymentMethod' => 'card',
        ]);

        $response->assertStatus(302);
    }
}
