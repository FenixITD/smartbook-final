<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Favorite;

use App\Dto\Favorite\FavoriteDto;
use App\Dto\Favorite\FavoriteResponseDto;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use App\Services\Favorite\CreateFavoriteService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class CreateFavoriteServiceTest extends TestCase
{
    private FavoriteRepositoryInterface&MockObject $repository;

    private CreateFavoriteService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(FavoriteRepositoryInterface::class);
        $this->service = new CreateFavoriteService($this->repository);
    }

    private function makeDto(): FavoriteDto
    {
        return new FavoriteDto(userId: 1, bookId: 5);
    }

    private function makeResponseDto(int $id = 1): FavoriteResponseDto
    {
        return new FavoriteResponseDto(
            id: $id,
            userId: 1,
            bookId: 5,
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

    public function test_execute_returns_favorite_response_dto(): void
    {
        $dto = $this->makeDto();
        $responseDto = $this->makeResponseDto(2);

        $this->repository
            ->method('create')
            ->willReturn($responseDto);

        $result = $this->service->execute($dto);

        $this->assertInstanceOf(FavoriteResponseDto::class, $result);
        $this->assertSame(2, $result->id);
        $this->assertSame(1, $result->userId);
        $this->assertSame(5, $result->bookId);
    }
}
