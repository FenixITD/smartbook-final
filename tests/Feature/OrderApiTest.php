<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'userId' => $this->user->id,
            'total' => 99.99,
            'status' => 'pending',
            'shippingAddress' => '123 Main St, London',
            'paymentMethod' => 'credit_card',
        ], $overrides);
    }

    // -----------------------------------------------------------------------
    // GET /api/orders
    // -----------------------------------------------------------------------

    public function test_get_list_returns_200_with_orders(): void
    {
        Order::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'userId', 'total', 'status', 'shippingAddress', 'paymentMethod', 'createdAt', 'updatedAt'],
                ],
            ]);
    }

    public function test_get_list_returns_empty_data_when_no_orders(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function test_get_list_respects_per_page_param(): void
    {
        Order::factory()->count(10)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/orders?perPage=3');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_get_list_sorts_by_total_desc(): void
    {
        Order::factory()->create(['total' => 10.00, 'user_id' => $this->user->id]);
        Order::factory()->create(['total' => 999.00, 'user_id' => $this->user->id]);

        $response = $this->getJson('/api/orders?sortBy=total&sortDirection=desc');

        $response->assertStatus(200);
        $data = $response->json('data');

        // JSON не разделяет int/float, поэтому используем assertEquals вместо assertSame
        $this->assertEquals(999.00, $data[0]['total']);
        $this->assertEquals(10.00, $data[1]['total']);
    }

    public function test_get_list_validates_sort_direction(): void
    {
        $response = $this->getJson('/api/orders?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_min(): void
    {
        $response = $this->getJson('/api/orders?perPage=0');

        $response->assertStatus(422);
    }

    public function test_get_list_validates_per_page_max(): void
    {
        $response = $this->getJson('/api/orders?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/orders/{order}
    // -----------------------------------------------------------------------

    public function test_get_by_id_returns_order(): void
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'paid',
        ]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'userId', 'total', 'status', 'shippingAddress', 'paymentMethod', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.status', 'paid');
    }

    public function test_get_by_id_returns_404_for_nonexistent_order(): void
    {
        $response = $this->getJson('/api/orders/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/orders
    // -----------------------------------------------------------------------

    public function test_create_order_returns_201_with_data(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'userId', 'total', 'status', 'shippingAddress', 'paymentMethod', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_create_order_persists_to_database(): void
    {
        $this->postJson('/api/orders', $this->validPayload(['status' => 'shipped']));

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'shipped',
        ]);
    }

    public function test_create_order_requires_user_id(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['userId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function test_create_order_requires_valid_user_id(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['userId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function test_create_order_requires_total(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['total' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total']);
    }

    public function test_create_order_total_cannot_be_negative(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['total' => -1]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total']);
    }

    public function test_create_order_total_cannot_exceed_max(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['total' => 10000.00]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total']);
    }

    public function test_create_order_requires_status(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['status' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_create_order_status_must_be_valid(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['status' => 'invalid_status']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_create_order_accepts_all_valid_statuses(): void
    {
        foreach (['pending', 'paid', 'shipped', 'delivered', 'cancelled'] as $status) {
            $response = $this->postJson('/api/orders', $this->validPayload(['status' => $status]));

            $response->assertStatus(201, "Failed for status: {$status}");
        }
    }

    public function test_create_order_requires_shipping_address(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['shippingAddress' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shippingAddress']);
    }

    public function test_create_order_shipping_address_max_255_characters(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['shippingAddress' => str_repeat('A', 256)]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shippingAddress']);
    }

    // -----------------------------------------------------------------------
    // PUT /api/orders/{order}
    // -----------------------------------------------------------------------

    public function test_update_order_returns_200_with_updated_data(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/orders/{$order->id}", $this->validPayload(['status' => 'delivered']));

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'delivered');
    }

    public function test_update_order_persists_changes_to_database(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $this->putJson("/api/orders/{$order->id}", $this->validPayload([
            'status' => 'cancelled',
            'shippingAddress' => '456 New Street, Manchester',
        ]));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
            'shipping_address' => '456 New Street, Manchester',
        ]);
    }

    public function test_update_order_returns_404_for_nonexistent_order(): void
    {
        $response = $this->putJson('/api/orders/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function test_update_order_requires_status(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/orders/{$order->id}", $this->validPayload(['status' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_update_order_status_must_be_valid(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/orders/{$order->id}", $this->validPayload(['status' => 'unknown']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_update_order_requires_valid_user_id(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/orders/{$order->id}", $this->validPayload(['userId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function test_update_order_total_cannot_be_negative(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/orders/{$order->id}", $this->validPayload(['total' => -5]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total']);
    }

    public function test_update_order_shipping_address_max_255_characters(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/orders/{$order->id}", $this->validPayload(['shippingAddress' => str_repeat('B', 256)]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shippingAddress']);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/orders/{order}
    // -----------------------------------------------------------------------

    public function test_delete_order_returns_200_with_message(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Order deleted successfully']);
    }

    public function test_delete_order_removes_from_database(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $this->deleteJson("/api/orders/{$order->id}");

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_delete_order_returns_404_for_nonexistent_order(): void
    {
        $response = $this->deleteJson('/api/orders/99999');

        $response->assertStatus(404);
    }
}
