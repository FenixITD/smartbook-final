<?php

declare(strict_types=1);

namespace Tests\Unit\Services\CartItem;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\CartItem\CreateCartItemService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class CreateCartItemServiceTest extends TestCase
{
    private CartItemRepositoryInterface&MockObject $repository;
    private CreateCartItemService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(CartItemRepositoryInterface::class);
        $this->service = new CreateCartItemService($this->repository);
    }

    private function makeDto(): CartItemDto
    {
        return new CartItemDto(userId: 1, bookId: 5, quantity: 2);
    }

    private function makeResponseDto(): CartItemResponseDto
    {
        return new CartItemResponseDto(
            id: 1,
            userId: 1,
            bookId: 5,
            quantity: 2,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }

    public function test_execute_calls_repository_create_with_dto(): void
    {
        $dto = $this->makeDto();
        $responseDto = $this->makeResponseDto();

        $this->repository
            ->expects($this->once())
            ->method('create')
            ->with($dto)
            ->willReturn($responseDto);

        $result = $this->service->execute($dto);

        $this->assertSame($responseDto, $result);
    }

    public function test_execute_returns_cart_item_response_dto(): void
    {
        $dto = $this->makeDto();
        $responseDto = $this->makeResponseDto();

        $this->repository
            ->method('create')
            ->willReturn($responseDto);

        $result = $this->service->execute($dto);

        $this->assertInstanceOf(CartItemResponseDto::class, $result);
        $this->assertSame(1, $result->id);
        $this->assertSame(2, $result->quantity);
    }
}
