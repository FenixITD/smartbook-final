<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    // -----------------------------------------------------------------------
    // GET /api/orders
    // -----------------------------------------------------------------------

    public function testGetListReturns200WithOrders(): void
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

    public function testGetListReturnsEmptyDataWhenNoOrders(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }

    public function testGetListRespectsPerPageParam(): void
    {
        Order::factory()->count(10)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/orders?perPage=3');

        $response->assertStatus(200);
        self::assertCount(3, $response->json('data'));
    }

    public function testGetListSortsByTotalDesc(): void
    {
        Order::factory()->create(['total' => 10.00, 'user_id' => $this->user->id]);
        Order::factory()->create(['total' => 999.00, 'user_id' => $this->user->id]);

        $response = $this->getJson('/api/orders?sortBy=total&sortDirection=desc');

        $response->assertStatus(200);
        $data = $response->json('data');

        // JSON не разделяет int/float, поэтому используем assertEquals вместо assertSame
        self::assertSame(999.00, $data[0]['total']);
        self::assertSame(10.00, $data[1]['total']);
    }

    public function testGetListValidatesSortDirection(): void
    {
        $response = $this->getJson('/api/orders?sortDirection=invalid');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMin(): void
    {
        $response = $this->getJson('/api/orders?perPage=0');

        $response->assertStatus(422);
    }

    public function testGetListValidatesPerPageMax(): void
    {
        $response = $this->getJson('/api/orders?perPage=101');

        $response->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // GET /api/orders/{order}
    // -----------------------------------------------------------------------

    public function testGetByIdReturnsOrder(): void
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

    public function testGetByIdReturns404ForNonexistentOrder(): void
    {
        $response = $this->getJson('/api/orders/99999');

        $response->assertStatus(404);
    }

    // -----------------------------------------------------------------------
    // POST /api/orders
    // -----------------------------------------------------------------------

    public function testCreateOrderReturns201WithData(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload());

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'userId', 'total', 'status', 'shippingAddress', 'paymentMethod', 'createdAt', 'updatedAt'],
            ])
            ->assertJsonPath('data.status', 'pending');
    }

    public function testCreateOrderPersistsToDatabase(): void
    {
        $this->postJson('/api/orders', $this->validPayload(['status' => 'shipped']));

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'shipped',
        ]);
    }

    public function testCreateOrderRequiresUserId(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['userId' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function testCreateOrderRequiresValidUserId(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['userId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function testCreateOrderRequiresTotal(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['total' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total']);
    }

    public function testCreateOrderTotalCannotBeNegative(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['total' => -1]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total']);
    }

    public function testCreateOrderTotalCannotExceedMax(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['total' => 10000.00]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total']);
    }

    public function testCreateOrderRequiresStatus(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['status' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function testCreateOrderStatusMustBeValid(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['status' => 'invalid_status']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function testCreateOrderAcceptsAllValidStatuses(): void
    {
        foreach (['pending', 'paid', 'shipped', 'delivered', 'cancelled'] as $status) {
            $response = $this->postJson('/api/orders', $this->validPayload(['status' => $status]));

            $response->assertStatus(201, "Failed for status: {$status}");
        }
    }

    public function testCreateOrderRequiresShippingAddress(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['shippingAddress' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shippingAddress']);
    }

    public function testCreateOrderShippingAddressMax255Characters(): void
    {
        $response = $this->postJson('/api/orders', $this->validPayload(['shippingAddress' => str_repeat('A', 256)]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shippingAddress']);
    }

    // -----------------------------------------------------------------------
    // PUT /api/orders/{order}
    // -----------------------------------------------------------------------

    public function testUpdateOrderReturns200WithUpdatedData(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/orders/{$order->id}", $this->validPayload(['status' => 'delivered']));

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'delivered');
    }

    public function testUpdateOrderPersistsChangesToDatabase(): void
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

    public function testUpdateOrderReturns404ForNonexistentOrder(): void
    {
        $response = $this->putJson('/api/orders/99999', $this->validPayload());

        $response->assertStatus(404);
    }

    public function testUpdateOrderRequiresStatus(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/orders/{$order->id}", $this->validPayload(['status' => '']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function testUpdateOrderStatusMustBeValid(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/orders/{$order->id}", $this->validPayload(['status' => 'unknown']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function testUpdateOrderRequiresValidUserId(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/orders/{$order->id}", $this->validPayload(['userId' => 99999]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['userId']);
    }

    public function testUpdateOrderTotalCannotBeNegative(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/orders/{$order->id}", $this->validPayload(['total' => -5]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total']);
    }

    public function testUpdateOrderShippingAddressMax255Characters(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/orders/{$order->id}", $this->validPayload(['shippingAddress' => str_repeat('B', 256)]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shippingAddress']);
    }

    // -----------------------------------------------------------------------
    // DELETE /api/orders/{order}
    // -----------------------------------------------------------------------

    public function testDeleteOrderReturns200WithMessage(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Order deleted successfully']);
    }

    public function testDeleteOrderRemovesFromDatabase(): void
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $this->deleteJson("/api/orders/{$order->id}");

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function testDeleteOrderReturns404ForNonexistentOrder(): void
    {
        $response = $this->deleteJson('/api/orders/99999');

        $response->assertStatus(404);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
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
}
