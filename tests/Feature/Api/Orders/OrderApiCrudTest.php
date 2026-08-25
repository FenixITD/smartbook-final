<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Orders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderApiCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_show_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin, 'sanctum')->getJson("/api/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.status', $order->status);
    }

    public function test_show_nonexistent_order_returns_404(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/orders/999999');

        $response->assertNotFound();
    }

    public function test_admin_can_update_order_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/orders/{$order->id}", [
            'userId' => $user->id,
            'status' => 'paid',
            'shippingAddress' => 'Updated Address',
            'paymentMethod' => 'card',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'paid');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
    }

    public function test_update_rejects_invalid_transition(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/orders/{$order->id}", [
            'userId' => $user->id,
            'status' => 'shipped',
            'shippingAddress' => '123 Main St',
            'paymentMethod' => 'card',
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
    }

    public function test_update_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/orders/{$order->id}", []);

        $response->assertUnprocessable();
    }

    public function test_non_admin_cannot_access_orders(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')->getJson("/api/orders/{$order->id}")->assertForbidden();
        $this->actingAs($user, 'sanctum')->getJson('/api/orders')->assertForbidden();
    }

    public function test_unauthenticated_cannot_access_orders(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertUnauthorized();
    }

    public function test_admin_can_delete_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/orders/{$order->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_delete_nonexistent_order_returns_404(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson('/api/orders/999999');

        $response->assertNotFound();
    }

    public function test_create_order_validates_required_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/orders', []);

        $response->assertUnprocessable();
    }
}
