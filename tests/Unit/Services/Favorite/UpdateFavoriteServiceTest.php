<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Favorite;

use App\Dto\Favorite\FavoriteDto;
use App\Dto\Favorite\FavoriteResponseDto;
use App\Models\Favorite;
use App\Repositories\Interfaces\FavoriteRepositoryInterface;
use App\Services\Favorite\UpdateFavoriteService;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class UpdateFavoriteServiceTest extends TestCase
{
    private FavoriteRepositoryInterface&MockObject $repository;

    private UpdateFavoriteService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(FavoriteRepositoryInterface::class);
        $this->service = new UpdateFavoriteService($this->repository);
    }

    private function makeDto(int $bookId = 5): FavoriteDto
    {
        return new FavoriteDto(userId: 1, bookId: $bookId);
    }

    private function makeResponseDto(int $id = 1, int $bookId = 5): FavoriteResponseDto
    {
        return new FavoriteResponseDto(
            id: $id,
            userId: 1,
            bookId: $bookId,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-06-01 00:00:00',
        );
    }

    public function test_execute_calls_repository_update_with_correct_arguments(): void
    {
        $favorite = new Favorite(['user_id' => 1, 'book_id' => 3]);
        $favorite->id = 1;

        $dto = $this->makeDto(5);
        $responseDto = $this->makeResponseDto(1, 5);

        $this->repository
            ->expects($this->once())
            ->method('update')
            ->with($favorite, $dto)
            ->willReturn($responseDto);

        $result = $this->service->execute($favorite, $dto);

        $this->assertSame($responseDto, $result);
    }

    public function test_execute_returns_updated_favorite_response_dto(): void
    {
        $favorite = new Favorite(['user_id' => 1, 'book_id' => 3]);
        $favorite->id = 2;

        $dto = $this->makeDto(10);
        $responseDto = $this->makeResponseDto(2, 10);

        $this->repository
            ->method('update')
            ->willReturn($responseDto);

        $result = $this->service->execute($favorite, $dto);

        $this->assertInstanceOf(FavoriteResponseDto::class, $result);
        $this->assertSame(10, $result->bookId);
        $this->assertSame(2, $result->id);
    }
}
