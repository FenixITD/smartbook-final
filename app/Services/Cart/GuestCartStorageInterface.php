<?php

declare(strict_types=1);

namespace App\Services\Cart;

interface GuestCartStorageInterface
{
    /**
     * @return array<int, array{book_id: int, quantity: int}>
     */
    public function getCart(): array;

    /**
     * @param array<int, array{book_id: int, quantity: int}> $cart
     */
    public function saveCart(array $cart): void;

    public function clear(): void;
}
