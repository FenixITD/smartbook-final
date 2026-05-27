<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api\CartItems;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Http\Controllers\Api\CartItems\CreateCartItemController;
use App\Http\Requests\CartItem\CartItemDataRequest;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class CreateCartItemControllerTest extends TestCase
{
    private MockInterface&CartItemRepositoryInterface $repository;
    private CreateCartItemController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(CartItemRepositoryInterface::class);
        $this->app->instance(CartItemRepositoryInterface::class, $this->repository);
        $this->controller = $this->app->make(CreateCartItemController::class);
    }

    public function test_returns_201_with_created_cart_item(): void
    {
        $responseDto = $this->makeResponseDto(id: 1, userId: 3, bookId: 4, quantity: 2);

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->andReturn($responseDto);

        $response = ($this->controller)($this->makeRequest(['userId' => 3, 'bookId' => 4, 'quantity' => 2]));

        $this->assertSame(201, $response->getStatusCode());
    }

    public function test_response_contains_created_cart_item_data(): void
    {
        $responseDto = $this->makeResponseDto(id: 5, userId: 3, bookId: 4, quantity: 2);

        $this->repository
            ->shouldReceive('create')
            ->andReturn($responseDto);

        $response = ($this->controller)($this->makeRequest(['userId' => 3, 'bookId' => 4, 'quantity' => 2]));
        $data = json_decode($response->getContent(), true)['data'];

        $this->assertSame(5, $data['id']);
        $this->assertSame(3, $data['userId']);
        $this->assertSame(4, $data['bookId']);
        $this->assertSame(2, $data['quantity']);
        $this->assertSame('2024-01-01 00:00:00', $data['createdAt']);
        $this->assertSame('2024-01-01 00:00:00', $data['updatedAt']);
    }

    public function test_passes_dto_from_request_to_repository(): void
    {
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (CartItemDto $arg) => $arg->userId === 7 && $arg->bookId === 12 && $arg->quantity === 3))
            ->andReturn($this->makeResponseDto(id: 1, userId: 7, bookId: 12, quantity: 3));

        ($this->controller)($this->makeRequest(['userId' => 7, 'bookId' => 12, 'quantity' => 3]));
    }

    private function makeRequest(array $data): CartItemDataRequest
    {
        return CartItemDataRequest::createFrom(
            Request::create('/api/cartItems', 'POST', $data)
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
