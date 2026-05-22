<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\PaginatedResponseDto;
use App\Dto\Review\ReviewDto;
use App\Dto\Review\ReviewFiltersDto;
use App\Dto\Review\ReviewResponseDto;

interface ReviewRepositoryInterface
{
    /** @return array<ReviewResponseDto> */
    public function getList(ReviewFiltersDto $filters): array;

    public function getWebList(ReviewFiltersDto $filters): PaginatedResponseDto;

    public function getById(int $id): ReviewResponseDto|null;

    public function findByIdWithRelations(int $id): ReviewResponseDto;

    /** @return array<ReviewResponseDto> */
    public function suggest(string $query): array;

    /** @param array<int> $ids
     *  @return array<ReviewResponseDto> */
    public function getByIds(array $ids): array;

    public function create(ReviewDto $data): ReviewResponseDto;

    public function update(int $id, ReviewDto $data): ReviewResponseDto|null;

    public function delete(int $id): bool;

    public function getByBookId(int $bookId, int $perPage = 10): PaginatedResponseDto;
}
