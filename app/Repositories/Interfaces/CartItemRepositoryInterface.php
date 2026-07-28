<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemFiltersDto;
use App\Dto\CartItem\CartItemResponseDto;
use App\Dto\CartItem\CartItemWithBookResponseDto;

interface CartItemRepositoryInterface
{
    /** @return array<CartItemResponseDto> */
    public function getList(CartItemFiltersDto $filters): array;

    public function getById(int $id): CartItemResponseDto|null;

    public function getTotalByUserId(int $userId): string;

    /** @return array<CartItemWithBookResponseDto> */
    public function getAllByUserId(int $userId): array;

    public function deleteByUserId(int $userId): void;

    public function addOrIncrement(CartItemDto $data): void;

    /** @param array<int, array{book_id: int, quantity: int}> $items */
    public function bulkAddOrIncrement(int $userId, array $items): void;

    public function create(CartItemDto $data): CartItemResponseDto;

    public function update(int $id, CartItemDto $data): CartItemResponseDto|null;

    public function updateByUserAndBook(int $userId, int $bookId, int $quantity): void;

    public function delete(int $id): bool;

    public function deleteByUserAndBook(int $userId, int $bookId): void;
}
