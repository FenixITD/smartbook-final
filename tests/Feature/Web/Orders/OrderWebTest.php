<?php declare(strict_types=1);

namespace Tests\Feature\Web\Orders;

use App\Models\Author;
use App\Models\Book;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use App\Services\Order\SearchOrderByQueryService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class OrderWebTest extends TestCase
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

    public function test_admin_can_view_orders_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->get('/orders');

        $response->assertStatus(200)->assertViewIs('orders.list');
    }

    public function test_admin_can_search_web_orders_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $mock = Mockery::mock(SearchOrderByQueryService::class);
        $mock->shouldReceive('searchPaginated')->andReturn([[$order->id], 1]);
        $this->app->instance(SearchOrderByQueryService::class, $mock);

        $response = $this->actingAs($admin)->get('/orders?search=test');

        $response->assertStatus(200)->assertViewIs('orders.list');
    }

    public function test_admin_can_view_order_create_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/orders/create');

        $response->assertStatus(200)->assertViewIs('orders.create');
    }

    public function test_admin_can_store_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id, 'price' => 20.00, 'stock' => 5, 'status' => 'active']);

        CartItem::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($admin)->post('/orders', [
            'userId' => $user->id,
            'status' => 'pending',
            'shippingAddress' => '789 Oak St',
            'paymentMethod' => 'webpay',
        ]);

        $response->assertRedirect(route('orders.index'));
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
            'shipping_address' => '789 Oak St',
        ]);
    }

    public function test_admin_can_view_order_edit_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->get("/orders/{$order->id}/edit");

        $response->assertStatus(200)->assertViewIs('orders.edit');
    }

    public function test_admin_can_update_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);

        $response = $this->actingAs($admin)->put("/orders/{$order->id}", [
            'userId' => $order->user_id,
            'status' => 'shipped',
            'shippingAddress' => 'New Address',
            'paymentMethod' => 'card',
        ]);

        $response->assertRedirect(route('orders.index'));
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
            'shipping_address' => 'New Address',
        ]);
    }

    public function test_admin_can_delete_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->delete("/orders/{$order->id}");

        $response->assertRedirect(route('orders.index'));
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_admin_can_view_single_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->get("/orders/{$order->id}");

        $response->assertStatus(200)->assertViewIs('orders.show');
    }
}
