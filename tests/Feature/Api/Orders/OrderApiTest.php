<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Orders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\Order\SearchSuggestOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_orders_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/orders');

        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_search_suggest_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $mock = Mockery::mock(SearchSuggestOrderService::class);
        $mock->shouldReceive('execute')
            ->once()
            ->with('pend')
            ->andReturn([
                ['id' => 1, 'user_name' => 'John', 'status' => 'pending', 'url' => 'http://localhost/orders/1'],
                ['id' => 2, 'user_name' => 'Jane', 'status' => 'pending', 'url' => 'http://localhost/orders/2'],
            ]);
        $this->app->instance(SearchSuggestOrderService::class, $mock);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.orders.suggest', ['q' => 'pend']));

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => ['id', 'user_name', 'status', 'url'],
            ]);
    }

    public function test_search_suggest_returns_empty_for_no_match(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $mock = Mockery::mock(SearchSuggestOrderService::class);
        $mock->shouldReceive('execute')
            ->once()
            ->with('zzz_nonexistent')
            ->andReturn([]);
        $this->app->instance(SearchSuggestOrderService::class, $mock);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.orders.suggest', ['q' => 'zzz_nonexistent']));

        $response->assertStatus(200)->assertJson([]);
    }

    public function test_search_suggest_rejects_short_query(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.orders.suggest', ['q' => 'a']));

        $response->assertStatus(422)->assertJsonValidationErrors(['q']);
    }

    public function test_search_suggest_requires_auth(): void
    {
        $response = $this->getJson(route('api.orders.suggest', ['q' => 'test']));

        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_access_suggest(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'sanctum')->getJson(route('api.orders.suggest', ['q' => 'test']));

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_pending_order_and_restore_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $book = Book::factory()->for(Author::factory())->create(['status' => 'active', 'stock' => 0]);
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        OrderItem::factory()->create(['order_id' => $order->id, 'book_id' => $book->id, 'quantity' => 10]);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'stock' => 10]);
    }
}
