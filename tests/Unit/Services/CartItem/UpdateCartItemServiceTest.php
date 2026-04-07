<?php

declare(strict_types=1);

namespace Tests\Unit\Services\CartItem;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\CartItem\UpdateCartItemService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class UpdateCartItemServiceTest extends TestCase
{
    private CartItemRepositoryInterface&MockObject $repository;
    private UpdateCartItemService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(CartItemRepositoryInterface::class);
        $this->service = new UpdateCartItemService($this->repository);
    }

    private function makeDto(int $quantity = 5): CartItemDto
    {
        return new CartItemDto(userId: 1, bookId: 5, quantity: $quantity);
    }

    private function makeResponseDto(int $id = 1, int $quantity = 5): CartItemResponseDto
    {
        return new CartItemResponseDto(
            id: $id,
            userId: 1,
            bookId: 5,
            quantity: $quantity,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-06-01 00:00:00',
        );
    }

    public function test_execute_calls_repository_update_with_correct_arguments(): void
    {
        $cartItem = new CartItem(['quantity' => 1]);
        $cartItem->id = 1;

        $dto = $this->makeDto(5);
        $responseDto = $this->makeResponseDto(1, 5);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with($cartItem, $dto)
            ->willReturn($responseDto);

        $result = $this->service->execute($cartItem, $dto);

        $this->assertSame($responseDto, $result);
    }

    public function test_execute_returns_updated_cart_item_response_dto(): void
    {
        $cartItem = new CartItem(['quantity' => 1]);
        $cartItem->id = 3;

        $dto = $this->makeDto(10);
        $responseDto = $this->makeResponseDto(3, 10);

        $this->repository
            ->method('update')
            ->willReturn($responseDto);

        $result = $this->service->execute($cartItem, $dto);

        $this->assertInstanceOf(CartItemResponseDto::class, $result);
        $this->assertSame(10, $result->quantity);
        $this->assertSame(3, $result->id);
    }
}
