<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Dto\CartItem\CartItemDto;
use App\Dto\CartItem\CartItemWithBookResponseDto;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Services\Interfaces\CartServiceInterface;
use Illuminate\Support\Facades\Auth;

final readonly class AuthCartService implements CartServiceInterface
{
    public function __construct(
        private CartItemRepositoryInterface $repository,
    ) {
    }

    /**
     * @return array<CartItemWithBookResponseDto>
     *
     * Retrieves detailed cart items including associated book data for the authenticated user.
     */
    public function getItems(): array
    {
        return $this->repository->getAllByUserId((int) Auth::id());
    }

    /**
     * @return float
     *
     * Calculates the total price of all items in the authenticated user's cart.
     */
    public function getTotal(): float
    {
        return $this->repository->getTotalByUserId((int) Auth::id());
    }

    /**
     * @param int $bookId
     * @param int $quantity
     * @return void
     *
     * Adds a new book or increments its quantity in the authenticated user's cart.
     */
    public function add(int $bookId, int $quantity): void
    {
        $this->repository->addOrIncrement(new CartItemDto(
            userId: (int) Auth::id(),
            bookId: $bookId,
            quantity: $quantity,
        ));

        activity('CartItem')
            ->withProperties(['user_id' => Auth::id(), 'book_id' => $bookId, 'quantity' => $quantity])
            ->log('added');
    }

    /**
     * @param int $bookId
     * @param int $quantity
     * @return void
     *
     * Updates the exact quantity of a specific book in the authenticated user's cart.
     */
    public function update(int $bookId, int $quantity): void
    {
        $this->repository->updateByUserAndBook((int) Auth::id(), $bookId, $quantity);
    }

    /**
     * @param int $bookId
     * @return void
     *
     * Removes a specific book entirely from the authenticated user's cart.
     */
    public function remove(int $bookId): void
    {
        $this->repository->deleteByUserAndBook((int) Auth::id(), $bookId);

        activity('CartItem')
            ->withProperties(['user_id' => Auth::id(), 'book_id' => $bookId])
            ->log('deleted');
    }

    /**
     * @return void
     *
     * Empties all items from the authenticated user's cart.
     */
    public function clear(): void
    {
        $this->repository->deleteByUserId((int) Auth::id());
    }

    /**
     * @return int
     *
     * Returns the total number of individual items currently in the authenticated user's cart.
     */
    public function count(): int
    {
        return $this->repository->countByUserId((int) Auth::id());
    }
}
