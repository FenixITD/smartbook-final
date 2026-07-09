<?php declare(strict_types=1);

namespace Tests\Feature\Api\Orders;

use App\Models\Order;
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
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $mock = Mockery::mock(SearchSuggestOrderService::class);
        $mock->shouldReceive('execute')->withAnyArgs()->andReturn([
            ['id' => $order->id, 'user_name' => 'User', 'status' => 'pending', 'url' => 'http://localhost/orders/1']
        ]);
        $this->app->instance(SearchSuggestOrderService::class, $mock);

        $response = $this->actingAs($admin, 'sanctum')->getJson(route('api.orders.suggest', ['q' => 'pend']));

        $response->assertStatus(200)->assertJsonPath('0.id', $order->id);
    }
}
