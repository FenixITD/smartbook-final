<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\CartItems;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Http\Controllers\Api\CartItems\UpdateCartItemController;
use App\Http\Requests\CartItem\CartItemDataRequest;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class UpdateCartItemControllerTest extends TestCase
{
    private MockInterface&CartItemRepositoryInterface $repository;
    private UpdateCartItemController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->app->instance(CartItemRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(UpdateCartItemController::class);
    }

    public function test_returns_200_with_updated_cart_item(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(4, Mockery::type(CartItemDto::class))
            ->andReturn($this->makeResponseDto(id: 4, userId: 3, bookId: 5, quantity: 10));

        $response = ($this->controller)($this->makeRequest(['userId' => 3, 'bookId' => 5, 'quantity' => 10]), 4);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_response_contains_updated_cart_item_data(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->andReturn($this->makeResponseDto(id: 4, userId: 3, bookId: 5, quantity: 10));

        $response = ($this->controller)($this->makeRequest(['userId' => 3, 'bookId' => 5, 'quantity' => 10]), 4);
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(4, $data['id']);
        $this->assertSame(3, $data['userId']);
        $this->assertSame(5, $data['bookId']);
        $this->assertSame(10, $data['quantity']);
    }

    public function test_passes_correct_id_and_dto_to_repository(): void
    {
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with(
                7,
                Mockery::on(fn (CartItemDto $arg) => $arg->userId === 2 && $arg->bookId === 9 && $arg->quantity === 5),
            )
            ->andReturn($this->makeResponseDto(id: 7, userId: 2, bookId: 9, quantity: 5));

        ($this->controller)($this->makeRequest(['userId' => 2, 'bookId' => 9, 'quantity' => 5]), 7);
    }

    private function makeRequest(array $data): CartItemDataRequest
    {
        return CartItemDataRequest::createFrom(
            Request::create('/api/cartItems/1', 'PUT', $data)
        );
    }

    private function makeResponseDto(int $id, int $userId, int $bookId, int $quantity): CartItemResponseDto
    {
        return new CartItemResponseDto(
            id: $id,
            userId: $userId,
            bookId: $bookId,
            quantity: $quantity,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }
}
