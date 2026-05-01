<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\Dto\CartItem\CartItemWithBookResponseDto;

interface CartServiceInterface
{
    /** @return array<int, array{book_id: int, quantity: int}> */
    public function getAll(): array;

    /** @return array<CartItemWithBookResponseDto> */
    public function getItems(): array;

    public function getTotal(): float;

    public function add(int $bookId, int $quantity): void;

    public function update(int $bookId, int $quantity): void;

    public function remove(int $bookId): void;

    public function clear(): void;

    public function count(): int;
}
