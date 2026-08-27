<?php declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Orders;

use App\Models\{Author, Book, CartItem, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_store_calculates_total_server_side_and_creates_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        $author = Author::factory()->create();

        $book1 = Book::factory()->create(['author_id' => $author->id, 'price' => 15.00, 'stock' => 5, 'status' => 'active']);
        $book2 = Book::factory()->create(['author_id' => $author->id, 'price' => 25.00, 'stock' => 5, 'status' => 'active']);

        CartItem::factory()->create(['user_id' => $customer->id, 'book_id' => $book1->id, 'quantity' => 2]);
        CartItem::factory()->create(['user_id' => $customer->id, 'book_id' => $book2->id, 'quantity' => 1]);

        $response = $this->actingAs($admin)->postJson('/api/orders', [
            'userId' => $customer->id,
            'status' => 'pending',
            'shippingAddress' => '456 API Ave',
            'paymentMethod' => 'webpay',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.total', '55.00');
        $response->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('orders', ['user_id' => $customer->id, 'total' => 55.00]);
        $this->assertDatabaseMissing('cart_items', ['user_id' => $customer->id]);
    }
}
