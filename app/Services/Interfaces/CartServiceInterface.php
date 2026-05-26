<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\Dto\CartItem\CartItemWithBookResponseDto;

interface CartServiceInterface
{
    /**
     * @return array<CartItemWithBookResponseDto>
     *
     * Retrieves detailed cart items including associated book data.
     */
    public function getItems(): array;

    /**
     * @return float
     *
     * Calculates and returns the total price of all items in the active cart.
     */
    public function getTotal(): float;

    /**
     * @param int $bookId
     * @param int $quantity
     * @return void
     *
     * Adds a specified quantity of a book to the cart or increments its existing quantity.
     */
    public function add(int $bookId, int $quantity): void;

    /**
     * @param int $bookId
     * @param int $quantity
     * @return void
     *
     * Updates the exact quantity of a specific book in the cart.
     */
    public function update(int $bookId, int $quantity): void;

    /**
     * @param int $bookId
     * @return void
     *
     * Removes a specific book entirely from the cart.
     */
    public function remove(int $bookId): void;

    /**
     * @return void
     *
     * Empties all items from the active cart.
     */
    public function clear(): void;

    /**
     * @return int
     *
     * Returns the total number of individual items currently in the cart.
     */
    public function count(): int;
}
